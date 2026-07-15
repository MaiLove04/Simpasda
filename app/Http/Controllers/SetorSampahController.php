<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SetorSampah;
use App\Models\DetailSetorSampah;
use App\Models\User;
use App\Models\MutasiSaldo;
use App\Models\JadwalPenjemputan;
use App\Models\MasterJadwalRutin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SetorSampahController extends Controller
{
    public function getRiwayatTotal($kurir_id)
    {
        $riwayat = SetorSampah::with(['nasabah', 'details.jenisSampah'])
                    ->where('kurir_id', $kurir_id)
                    ->latest()
                    ->get();

        return response()->json($riwayat, 200);
    }

    public function setorJadwalAdmin(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id'     => 'required|integer',
            'grand_total' => 'required|numeric',
            'sampah_list' => 'required',
            'jadwal_id'   => 'nullable|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal Validasi Input: ' . implode(', ', $validator->errors()->all())
            ], 422);
        }

        DB::beginTransaction();

        try {
            $cleanId = trim($id, '{} ');
            $setor = SetorSampah::where('jadwal_id', $cleanId)->first();
            
            if (!$setor) {
                $setor = new SetorSampah();
                $setor->jadwal_id = $cleanId;
            }

            $sampahList = json_decode($request->sampah_list, true) ?? [];
            $itemPertama = count($sampahList) > 0 ? $sampahList[0] : null;

            $setor->user_id   = $request->user_id;
            $setor->kurir_id  = $request->kurir_id;
            $setor->total     = $request->grand_total;
            $setor->catatan   = $request->catatan ?? 'Selesai ditimbang oleh kurir lapangan (Jadwal Admin)';
            $setor->status    = 'selesai';
            $setor->jenis_sampah_id = $itemPertama ? $itemPertama['jenis_sampah_id'] : null;
            $setor->berat           = $itemPertama ? $itemPertama['berat'] : 0;
            $setor->harga_per_kg    = $itemPertama ? ($itemPertama['harga_per_kg'] ?? 0) : 0;
            $setor->save(); 

            $this->simpanDetailSampah($setor->id, $request->sampah_list);
            $this->tambahSaldoNasabah($request->user_id, $request->grand_total);
            $this->catatMutasi($request->user_id, $setor->id, $request->grand_total);

            $jadwalHarian = JadwalPenjemputan::where('id', $cleanId)->first();
            if ($jadwalHarian) {
                $jadwalHarian->update(['status' => 'selesai']);
            }

            $masterJadwal = MasterJadwalRutin::where('nasabah_id', (int)$request->user_id)->first();
            
            if ($masterJadwal) {
                $interval = (int)($masterJadwal->interval_hari ?? 2);
                
                if ($masterJadwal->tipe_jadwal === 'interval') {
                    $nextDate = Carbon::today()->addDays($interval)->toDateString();
                } else {
                    $nextDate = Carbon::today()->addDays(7)->toDateString();
                }

                DB::statement("UPDATE master_jadwal_rutins SET tanggal_penjemputan_berikutnya = ? WHERE nasabah_id = ?", [
                    $nextDate, 
                    (int)$request->user_id
                ]);
            }

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Setoran dari jadwal admin sukses diproses oleh kurir!',
                'data' => $setor->load('details')
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses setoran: ' . $e->getMessage()
            ], 500);
        }
    }

    public function setorRequestNasabah(Request $request, $setor_sampah_id)
    {
        $validator = Validator::make($request->all(), [
            'user_id'     => 'required|integer',
            'grand_total' => 'required|numeric',
            'sampah_list' => 'required',
            'jadwal_id'   => 'nullable|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal Validasi Input: ' . implode(', ', $validator->errors()->all())
            ], 422);
        }

        DB::beginTransaction();

        try {
            $setor = SetorSampah::findOrFail($setor_sampah_id);
            $sampahList = json_decode($request->sampah_list, true) ?? [];
            $itemPertama = count($sampahList) > 0 ? $sampahList[0] : null;

            $setor->user_id  = $request->user_id;
            $setor->kurir_id = $request->kurir_id;
            $setor->total    = $request->grand_total;
            $setor->catatan  = $request->catatan ?? 'Berat di-update dan diselesaikan oleh kurir lapangan';
            $setor->status   = 'selesai';
            $setor->jenis_sampah_id = $itemPertama ? $itemPertama['jenis_sampah_id'] : $setor->jenis_sampah_id;
            $setor->berat           = $itemPertama ? $itemPertama['berat'] : $setor->berat;
            $setor->harga_per_kg    = $itemPertama ? ($itemPertama['harga_per_kg'] ?? 0) : $setor->harga_per_kg;
            $setor->save();

            $this->simpanDetailSampah($setor->id, $request->sampah_list);
            $this->tambahSaldoNasabah($request->user_id, $request->grand_total);
            $this->catatMutasi($request->user_id, $setor->id, $request->grand_total);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berat request nasabah berhasil diperbarui dan diselesaikan!',
                'data' => $setor->load('details')
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui berat request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function requestPenjemputan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'items'   => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal Validasi: ' . implode(', ', $validator->errors()->all())
            ], 422);
        }

        if ($this->cekJadwalHariIni($request->user_id, Carbon::today()->toDateString())) {
            return response()->json([
                'success' => false,
                'message' => 'Maaf, Anda tidak dapat melakukan request mandiri. Hari ini rumah Anda sudah masuk ke dalam daftar plot jadwal penjemputan rutin/petugas.'
            ], 422);
        }

        $items = $request->input('items', []);
        if (is_string($items)) {
            $items = json_decode($items, true) ?? [];
        }

        if (empty($items)) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori sampah belum dipilih.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $itemPertama = $items[0];

            $setor = new SetorSampah();
            $setor->user_id = $request->user_id;
            $setor->catatan = $request->catatan ?? 'Permintaan penjemputan mandiri oleh nasabah';
            $setor->status  = 'pending';
            $setor->jenis_sampah_id = $itemPertama['jenis_sampah_id'] ?? null;
            $setor->berat           = 0;
            $setor->harga_per_kg    = 0;
            $setor->total           = 0;
            $setor->save(); 

            foreach ($items as $item) {
                $detail = new DetailSetorSampah();
                $detail->setor_sampah_id = $setor->id;
                $detail->jenis_sampah_id = $item['jenis_sampah_id'];
                $detail->berat           = 0;
                $detail->harga_per_kg    = 0;
                $detail->subtotal        = 0;
                $detail->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Permintaan penjemputan sampah berhasil dibuat!'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses ke database: ' . $e->getMessage()
            ], 500);
        }
    }

    private function cekJadwalHariIni($userId, $tanggalTarget)
    {
        $date = Carbon::parse($tanggalTarget)->toDateString();

        $adaJadwalHarian = JadwalPenjemputan::where('nasabah_id', $userId)
            ->whereDate('tanggal_penjemputan', $date)
            ->whereIn('status', ['terjadwal', 'proses'])
            ->exists();

        if ($adaJadwalHarian) {
            return true; 
        }

        $adaMasterHariIni = MasterJadwalRutin::where('nasabah_id', $userId)
            ->where('is_aktif', true)
            ->whereDate('tanggal_penjemputan_berikutnya', $date)
            ->exists();

        return $adaMasterHariIni;
    }

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

    public function showRequestDetail($nasabah_id)
    {
        try {
            $setorMaster = SetorSampah::where('user_id', $nasabah_id)
                ->whereNull('jadwal_id')
                ->where('status', 'pending')
                ->latest()
                ->first();

            if (!$setorMaster) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada manifes request aktif untuk nasabah ini.',
                    'setor_sampah_id' => '',
                    'items' => []
                ], 200);
            }

            $details = DetailSetorSampah::where('setor_sampah_id', $setorMaster->id)
                ->with('jenisSampah')
                ->get();

            $formAutoload = [];
            foreach ($details as $item) {
                if ($item->jenisSampah) {
                    $hargaBeli = $item->harga_per_kg ?? $item->jenisSampah->harga_per_kg ?? 0;

                    $formAutoload[] = [
                        'jenis_sampah_id' => (int) $item->jenis_sampah_id,
                        'nama_sampah'     => (string) $item->jenisSampah->nama,
                        'harga_per_kg'    => (int) $hargaBeli,
                        'berat'           => 0.0, 
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
                'message' => 'Gagal meload data request: ' . $e->getMessage(),
                'setor_sampah_id' => '',
                'items' => []
            ], 500);
        }
    }

    private function simpanDetailSampah($setorId, $sampahListJson)
    {
        $sampahList = json_decode($sampahListJson, true);
        if (is_array($sampahList)) {
            DetailSetorSampah::where('setor_sampah_id', $setorId)->delete();

            foreach ($sampahList as $item) {
                $detail = new DetailSetorSampah();
                $detail->setor_sampah_id = $setorId;
                $detail->jenis_sampah_id = $item['jenis_sampah_id'];
                $detail->berat           = $item['berat'];
                $detail->harga_per_kg    = $item['harga_per_kg'];
                $detail->subtotal        = $item['total_item']; 
                $detail->save();
            }
        }
    }

    private function tambahSaldoNasabah($userId, $grandTotal)
    {
        $nasabah = User::find($userId);
        if ($nasabah) {
            $nasabah->saldo = $nasabah->saldo + (int)$grandTotal;
            $nasabah->save();
        }
    }

    private function catatMutasi($userId, $referensiId, $grandTotal)
    {
        $mutasi = new MutasiSaldo();
        $mutasi->user_id         = $userId;
        $mutasi->jenis_transaksi = 'masuk';
        $mutasi->sumber          = 'setor_sampah';
        $mutasi->referensi_id    = $referensiId;
        $mutasi->nominal         = (int) $grandTotal;
        $mutasi->status          = 'success';
        $mutasi->keterangan      = 'Uang masuk dari penimbangan sampah lapangan';
        $mutasi->save();
    }

    public function index()
    {
        try {
            $data = SetorSampah::with(['nasabah', 'kurir', 'details.jenisSampah'])->latest()->get();
            return response()->json([
                'success' => true,
                'message' => 'Data setor sampah berhasil dimuat.',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Gunakan endpoint /api/request-penjemputan atau fitur Setor Jadwal.'
        ], 400);
    }
}