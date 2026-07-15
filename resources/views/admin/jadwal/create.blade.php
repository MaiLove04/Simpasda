@extends('layouts.admin')

@section('content')
<div class="container-fluid px-2 py-2">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1" style="color: #0f172a; font-weight: bold;">Tambah Jadwal Penjemputan</h1>
            <p class="text-muted mb-0" style="font-size: 14px;">Buat jadwal penjemputan baru dan tentukan kurir yang bertugas.</p>
        </div>
        <a href="/admin/jadwal" class="btn btn-secondary" style="border-radius: 8px; font-weight: 500; padding: 10px 20px;">
            Kembali
        </a>
    </div>

    <div class="card shadow-sm border-0 p-4" style="border-radius: 16px; background: white; max-width: 700px;">
        <form action="/admin/jadwal" method="POST">
            @csrf
            
            {{-- 🛠️ TAMBAHAN: Amankan ID Bank Sampah induk secara otomatis (Default ID: 1) --}}
            <input type="hidden" name="bank_sampah_id" value="1">

            <div class="mb-3">
                <label for="nasabah_select" class="form-label" style="font-weight: 600; color: #0f172a;">Pilih Nasabah</label>
                <select class="form-select @error('nasabah_id') is-invalid @enderror" id="nasabah_select" name="nasabah_id" style="border-radius: 8px; padding: 10px;" required>
                    <option value="" selected disabled>-- Pilih Nasabah Bank Sampah --</option>
                    @foreach($nasabahs as $nasabah)
                        <option value="{{ $nasabah->id }}" data-alamat="{{ $nasabah->alamat }}">{{ $nasabah->name }}</option>
                    @endforeach
                </select>
                @error('nasabah_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="alamat" class="form-label" style="font-weight: 600; color: #0f172a;">Alamat Penjemputan</label>
                <input type="text" class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" placeholder="Alamat akan terisi otomatis setelah memilih nasabah" style="border-radius: 8px; padding: 10px;" required>
                @error('alamat')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="kurir_id" class="form-label" style="font-weight: 600; color: #0f172a;">Tugaskan Kurir</label>
                <select class="form-select @error('kurir_id') is-invalid @enderror" id="kurir_id" name="kurir_id" style="border-radius: 8px; padding: 10px;" required>
                    <option value="" selected disabled>-- Pilih Kurir Yang Bertugas --</option>
                    @foreach($kurirs as $kurir)
                        <option value="{{ $kurir->id }}">{{ $kurir->name }}</option>
                    @endforeach
                </select>
                @error('kurir_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="tanggal_penjemputan" class="form-label" style="font-weight: 600; color: #0f172a; mb-0">Tanggal & Waktu Penjemputan</label>
                {{-- 💡 INDIKATOR BARU: Memberi kejelasan mutlak kepada admin --}}
                
                <input 
                    type="datetime-local" 
                    class="form-control @error('tanggal_penjemputan') is-invalid @enderror" 
                    id="tanggal_penjemputan" 
                    name="tanggal_penjemputan" 
                    min="{{ date('Y-m-d\TH:i') }}" {{-- Mencegah admin memilih waktu yang sudah lewat dari menit ini --}}
                    style="border-radius: 8px; padding: 10px;" 
                    required
                >
                @error('tanggal_penjemputan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="catatan" class="form-label" style="font-weight: 600; color: #0f172a;">Catatan Tambahan (Opsional)</label>
                <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Contoh: Rumah pagar hitam, ketuk pintu dulu, dll." style="border-radius: 8px; padding: 10px;"></textarea>
            </div>

            <button type="submit" class="btn btn-primary w-100" style="background-color: #16a34a; border: none; padding: 12px; border-radius: 8px; font-weight: 600; font-size: 16px;">
                Buat Jadwal & Kirim ke Kurir
            </button>
        </form>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function() {
        $('#nasabah_select').change(function() {
            var alamatNasabah = $(this).find(':selected').data('alamat');
            $('#alamat').val(alamatNasabah ? alamatNasabah : '');
        });
    });
</script>
@endsection