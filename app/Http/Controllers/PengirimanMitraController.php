<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengirimanMitra;
use App\Models\DetailPengirimanMitra;
use App\Models\Mitra;
use Illuminate\Support\Str;
use DB;
use App\Models\Operasional;

class PengirimanMitraController extends Controller
{
    // LIST PENGIRIMAN
    public function index(Request $request)
    {
        $query = PengirimanMitra::with('mitra');

        // Cari nama mitra / kode pengiriman
        if ($request->filled('search')) {

            $query->where(function ($q) use ($request) {

                $q->where('kode_pengiriman', 'like', '%' . $request->search . '%')
                ->orWhereHas('mitra', function ($mitra) use ($request) {

                        $mitra->where('nama_mitra', 'like', '%' . $request->search . '%');

                });

            });

        }

        // Filter status pengiriman
        if ($request->filled('status_pengiriman')) {

            $query->where('status_pengiriman', $request->status_pengiriman);

        }

        // Filter status pembayaran
        if ($request->filled('status_pembayaran')) {

            $query->where('status_pembayaran', $request->status_pembayaran);

        }

        // Filter bulan
        if ($request->filled('bulan')) {

            $query->whereMonth('tanggal', date('m', strtotime($request->bulan)))
                ->whereYear('tanggal', date('Y', strtotime($request->bulan)));

        }

        // Pagination
        $data = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();
        
        // Card Statistik
        $totalPengiriman = PengirimanMitra::count();

        $menunggu = PengirimanMitra::where('status_pengiriman','Menunggu Mitra')->count();

        $diterima = PengirimanMitra::where('status_pengiriman','Diterima')->count();

        $lunas = PengirimanMitra::where('status_pembayaran','Lunas')->count();

        return view(
            'admin.pengiriman-mitra.index',
            compact(
                'data',
                'totalPengiriman',
                'menunggu',
                'diterima',
                'lunas'
            )
        );
    }

    // FORM CREATE
    public function create()
    {
        $mitras = Mitra::where('status', 'Aktif')->get();
        return view('admin.pengiriman-mitra.create', compact('mitras'));
    }

    // SIMPAN PENGIRIMAN + DETAIL
    public function store(Request $request)
    {
        $request->validate([
            'mitra_id' => 'required',
            'tanggal' => 'required|date',
        ]);

        DB::beginTransaction();

        try {

            // 1. CREATE PENGIRIMAN
            $pengiriman = PengirimanMitra::create([
                'kode_pengiriman' => 'PM-' . Str::upper(Str::random(6)),
                'mitra_id' => $request->mitra_id,
                'tanggal' => $request->tanggal,
                'total' => 0,
                'status_pengiriman' => 'Menunggu Mitra',
                'status_pembayaran' => 'Belum Bayar',
            ]);

            $total = 0;

            // 2. LOOP DETAIL BARANG
            foreach ($request->details as $item) {

                $subtotal = $item['berat'] * $item['harga'];

                DetailPengirimanMitra::create([
                    'pengiriman_id' => $pengiriman->id,
                    'jenis_sampah' => $item['jenis_sampah'],
                    'berat' => $item['berat'],
                    'harga' => $item['harga'],
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            // 3. UPDATE TOTAL
            $pengiriman->update([
                'total' => $total
            ]);

            DB::commit();

            return redirect()
                ->route('pengiriman-mitra.index')
                ->with('success', 'Pengiriman berhasil dibuat');

        } catch (\Exception $e) {
            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    // DETAIL PENGIRIMAN
    public function show($id)
    {
        $data = PengirimanMitra::with('mitra', 'details')->findOrFail($id);
        return view('admin.pengiriman-mitra.show', compact('data'));
    }

    public function edit($id)
    {
        $pengiriman = PengirimanMitra::with('details')->findOrFail($id);

        // kalau sudah diproses tidak boleh diedit
        if ($pengiriman->status_pengiriman != 'Menunggu Mitra') {
            return redirect()
                ->route('pengiriman-mitra.index')
                ->with('error', 'Pengiriman yang sudah diproses tidak dapat diedit.');
        }

        $mitras = Mitra::where('status', 'Aktif')->get();

        return view(
            'admin.pengiriman-mitra.edit',
            compact('pengiriman', 'mitras')
        );
    }



    // MITRA KONFIRMASI BARANG DITERIMA
    public function terima($id)
    {
        $pengiriman = PengirimanMitra::findOrFail($id);

        $pengiriman->update([
            'status_pengiriman' => 'Diterima'
        ]);

        return back()->with('success', 'Barang berhasil dikonfirmasi diterima');
    }

    // MITRA PILIH PEMBAYARAN
    public function pembayaran(Request $request, $id)
    {
        $request->validate([
            'metode_pembayaran' => 'required|in:Cash,Transfer',

            'bukti_transfer' => [
                'required_if:metode_pembayaran,Transfer',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:2048'
            ],
        ],[
            'bukti_transfer.required_if' => 'Bukti pembayaran wajib diupload.',
            'bukti_transfer.mimes' => 'File harus berupa JPG, JPEG, PNG atau PDF.',
            'bukti_transfer.max' => 'Ukuran file maksimal 2 MB.',
        ]);

        $pengiriman = PengirimanMitra::findOrFail($id);

        $data = [
            'metode_pembayaran' => $request->metode_pembayaran,
            'status_pembayaran' => 'Menunggu Verifikasi',
            'tanggal_pembayaran' => now(),
        ];

        if ($request->hasFile('bukti_transfer')) {

            $path = $request->file('bukti_transfer')->store(
                'bukti-transfer',
                'public'
            );

            $data['bukti_transfer'] = $path;
        }

        $pengiriman->update($data);

        return back()->with(
            'success',
            'Pembayaran berhasil dikirim dan menunggu verifikasi admin.'
        );
    }

    // ADMIN VERIFIKASI LUNAS
    public function verifikasi($id)
    {
        $pengiriman = PengirimanMitra::findOrFail($id);

        DB::beginTransaction();

        try {

            // Update status pengiriman
            $pengiriman->update([
                'status_pembayaran' => 'Lunas',
                'status_pengiriman' => 'Selesai'
            ]);

            // Cek supaya tidak dobel masuk operasional
            $cek = Operasional::where('kode_referensi', $pengiriman->kode_pengiriman)
                        ->first();

            if (!$cek) {

                Operasional::create([

                    'jenis_transaksi' => 'Pemasukan',

                    'kategori' => 'Pembayaran Mitra',

                    'harga' => $pengiriman->total,

                    'jumlah' => 1,

                    'total' => $pengiriman->total,

                    'keterangan' =>
                        'Pembayaran Pengiriman ' .
                        $pengiriman->kode_pengiriman,

                    'sumber' => 'Otomatis',

                    'tanggal' => now(),

                    'kode_referensi' => $pengiriman->kode_pengiriman,

                ]);

            }

            DB::commit();

            return back()->with(
                'success',
                'Pembayaran berhasil diverifikasi.'
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with(
                'error',
                $e->getMessage()
            );

        }
    }

    public function update(Request $request, $id)
    {
        $pengiriman = PengirimanMitra::findOrFail($id);

        if ($pengiriman->status_pengiriman != 'Menunggu Mitra') {
            return back()->with('error', 'Tidak bisa diubah');
        }

        $pengiriman->update([
            'mitra_id' => $request->mitra_id,
            'tanggal' => $request->tanggal,
        ]);

        // HAPUS DETAIL LAMA
        $pengiriman->details()->delete();

        $total = 0;

        foreach ($request->details as $item) {
            $subtotal = $item['berat'] * $item['harga'];

            $pengiriman->details()->create([
                'jenis_sampah' => $item['jenis_sampah'],
                'berat' => $item['berat'],
                'harga' => $item['harga'],
                'subtotal' => $subtotal,
            ]);

            $total += $subtotal;
        }

        $pengiriman->update([
            'total' => $total
        ]);

        return redirect()->route('pengiriman-mitra.index')
            ->with('success', 'Data berhasil diupdate');
    }

    public function destroy($id)
    {
        $pengiriman = PengirimanMitra::findOrFail($id);

        // Tidak boleh dihapus jika sudah diproses
        if ($pengiriman->status_pengiriman != 'Menunggu Mitra') {

            return back()->with(
                'error',
                'Pengiriman yang sudah diproses tidak dapat dihapus.'
            );
        }

        DB::beginTransaction();

        try {

            // hapus detail dulu
            $pengiriman->details()->delete();

            // hapus header
            $pengiriman->delete();

            DB::commit();

            return redirect()
                ->route('pengiriman-mitra.index')
                ->with('success','Pengiriman berhasil dihapus.');

        } catch (\Exception $e){

            DB::rollBack();

            return back()->with('error',$e->getMessage());

        }
    }
}