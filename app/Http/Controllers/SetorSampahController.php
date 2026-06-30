<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SetorSampah;
use App\Models\DetailSetorSampah;
use App\Models\User;
use App\Models\MutasiSaldo;
use App\Models\JadwalPenjemputan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SetorSampahController extends Controller
{
    /**
     * 📋 AMBIL RIWAYAT BERDASARKAN KURIR (UNTUK FLUTTER)
     */
    public function getRiwayatTotal($kurir_id)
    {
        $riwayat = SetorSampah::with(['nasabah', 'details.jenisSampah'])
                    ->where('kurir_id', $kurir_id)
                    ->latest()
                    ->get();

        return response()->json($riwayat, 200);
    }

    /**
     * ⚖️ STORE SIMPAN TIMBANGAN KURIR: OTOMATISASI SALDO & UPDATE JADWAL SELESAI
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'user_id'         => 'required',
            'grand_total'     => 'required',
            'sampah_list'     => 'required',
            'foto_sampah'     => 'nullable',
            'setor_sampah_id' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal Validasi Input: ' . implode(', ', $validator->errors()->all())
            ], 422);
        }

        DB::beginTransaction();

        try {
            // 1. Olah upload gambar bukti penimbangan sampah jika ada
            $pathFoto = null;
            if ($request->hasFile('foto_sampah')) {
                $file = $request->file('foto_sampah');
                $namaFile = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/sampah'), $namaFile);
                $pathFoto = 'uploads/sampah/' . $namaFile;
            }

            // 2. LOGIKA UTAMA: Cek data induk gantung
            if ($request->filled('setor_sampah_id')) {
                $setor = SetorSampah::find($request->setor_sampah_id);
                if (!$setor) {
                    return response()->json(['success' => false, 'message' => 'Data transaksi induk tidak ditemukan.'], 404);
                }
            } else {
                $setor = new SetorSampah();
            }

            $setor->user_id = $request->user_id;

            $defaultKurir = User::where('role', 'kurir')->first();
            $setor->kurir_id = $request->kurir_id ?? ($defaultKurir ? $defaultKurir->id : 14);

            $setor->total = $request->grand_total;
            
            if ($pathFoto) {
                $setor->foto_sampah = $pathFoto;
            } else {
                $setor->foto_sampah = ""; 
            }
            
            $setor->catatan = $request->catatan ?? 'Selesai ditimbang oleh kurir lapangan';
            $setor->status = 'selesai'; 
            $setor->save();

            // 3. Simpan ke Details (Tabel Anak)
            $sampahList = json_decode($request->sampah_list, true);
            if (!is_array($sampahList)) {
                return response()->json(['success' => false, 'message' => 'Format sampah_list tidak valid.'], 400);
            }

            DetailSetorSampah::where('setor_sampah_id', $setor->id)->delete();

            foreach ($sampahList as $item) {
                $detail = new DetailSetorSampah();
                $detail->setor_sampah_id = $setor->id;
                $detail->jenis_sampah_id = $item['jenis_sampah_id'];
                $detail->berat           = $item['berat'];
                $detail->harga_per_kg    = $item['harga_per_kg'];
                $detail->subtotal        = $item['total_item'];
                $detail->save();
            }

            // 4. PROSES FINANSIAL: UPDATE SALDO NASABAH
            $nasabah = User::find($request->user_id);
            if ($nasabah) {
                $nominalMasuk = (int) $request->grand_total;
                $nasabah->saldo = $nasabah->saldo + $nominalMasuk;
                $nasabah->save();
            }

            // 5. CATAT HISTORI KE TABEL MUTASI SALDO
            $mutasi = new MutasiSaldo();
            $mutasi->user_id         = $request->user_id;
            $mutasi->jenis_transaksi = 'masuk';
            $mutasi->sumber          = 'setor_sampah';
            $mutasi->referensi_id    = $setor->id;
            $mutasi->nominal         = (int) $request->grand_total;
            $mutasi->status          = 'success';
            $mutasi->keterangan      = 'Uang masuk dari penimbangan sampah multi-item';
            $mutasi->save();

            // 6. SINKRONISASI UPDATE STATUS JADWAL JADI SELESAI (Notifikasi Di-disable)
            if ($request->has('jadwal_id') && !empty($request->jadwal_id)) {
                $jadwal = JadwalPenjemputan::find($request->jadwal_id);
                if ($jadwal) {
                    $jadwal->status = 'selesai';
                    $jadwal->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '✅ Setoran sukses diproses! Data sinkron, saldo bertambah.',
                'data' => $setor->load('details')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses setoran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📊 AMBIL DATA COUNTER REAL-TIME UNTUK DASHBOARD KURIR
     */
    public function getDashboardKurir($kurir_id)
    {
        try {
            $totalTransaksi = SetorSampah::where('kurir_id', $kurir_id)->count();
            $setorIds = SetorSampah::where('kurir_id', $kurir_id)->pluck('id');
            $totalKgSampah = DetailSetorSampah::whereIn('setor_sampah_id', $setorIds)->sum('berat');
            $totalUangDiproses = SetorSampah::where('kurir_id', $kurir_id)->sum('total');

            return response()->json([
                'success'             => true,
                'kurir_id'            => $kurir_id,
                'total_transaksi'     => $totalTransaksi,
                'total_kg_sampah'     => round($totalKgSampah, 2),
                'total_uang_diproses' => (int) $totalUangDiproses
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat ringkasan dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📥 ALUR FINAL NASABAH: REQUEST JEMPUT
     */
    public function requestPenjemputan(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'items'   => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            $nasabah = User::find($request->user_id);
            $kurirAktif = User::where('role', 'kurir')->first();
            $kurirIdDefault = $kurirAktif ? $kurirAktif->id : 14;

            $jadwal = new JadwalPenjemputan();
            $jadwal->nasabah_id = $request->user_id;
            $jadwal->kurir_id = $kurirIdDefault;
            $jadwal->bank_sampah_id = 1; 
            $jadwal->alamat = $nasabah->alamat ?? 'Alamat tidak diisi nasabah';
            $jadwal->tanggal_penjemputan = Carbon::today()->format('Y-m-d');
            $jadwal->status = 'terjadwal'; 
            $jadwal->catatan = $request->catatan ?? 'Disetor lewat aplikasi nasabah'; 
            $jadwal->save();

            $setor = new SetorSampah();
            $setor->user_id = $request->user_id;
            $setor->kurir_id = $kurirIdDefault;
            $setor->total = 0; 
            $setor->foto_sampah = null; 
            $setor->catatan = $request->catatan ?? 'Request jemput lewat aplikasi';
            $setor->status = 'menunggu_verifikasi'; 
            $setor->save();

            foreach ($request->items as $item) {
                if (isset($item['jenis_sampah_id'])) {
                    $detail = new DetailSetorSampah();
                    $detail->setor_sampah_id = $setor->id; 
                    $detail->jenis_sampah_id = $item['jenis_sampah_id'];
                    $detail->berat           = null; 
                    $detail->harga_per_kg    = null; 
                    $detail->subtotal        = 0;
                    $detail->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '✅ Request penjemputan & manifes kategori sampah berhasil didaftarkan!',
                'data' => [
                    'jadwal' => $jadwal,
                    'setor'  => $setor->load('details')
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses di server: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔍 AMBIL MANIFES REQUEST NASABAH
     */
    public function showRequestDetail($nasabah_id)
    {
        try {
            $setorMaster = SetorSampah::where('user_id', $nasabah_id)
                ->where('status', 'menunggu_verifikasi')
                ->latest()
                ->first();

            if (!$setorMaster) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada manifes request aktif untuk nasabah ini.'
                ], 404);
            }

            $details = DetailSetorSampah::where('setor_sampah_id', $setorMaster->id)
                ->with('jenisSampah') 
                ->get();

            $formAutoload = [];
            foreach ($details as $item) {
                if ($item->jenisSampah) {
                    $hargaBeliTerupdate = $item->jenisSampah->harga_beli
                                        ?? $item->jenisSampah->harga
                                        ?? $item->jenisSampah->harga_per_kg
                                        ?? 2000; 

                    $formAutoload[] = [
                        'jenis_sampah_id' => $item->jenis_sampah_id,
                        'nama_sampah'     => $item->jenisSampah->nama,
                        'harga_per_kg'    => (int) $hargaBeliTerupdate,
                        'berat'           => 0,
                        'total_item'      => 0
                    ];
                }
            }

            return response()->json([
                'success'         => true,
                'setor_sampah_id' => $setorMaster->id, 
                'items'           => $formAutoload
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal meload data request: ' . $e->getMessage()
            ], 500);
        }
    }
}