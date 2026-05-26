@extends('layouts.admin')

@section('content')
<div class="container-fluid px-2 py-2">
    <div class="mb-4">
        <h1 class="h2 mb-1" style="color: #0f172a; font-weight: bold;">Tambah Pola Rutin</h1>
        <p class="text-muted mb-0" style="font-size: 14px;">Daftarkan jadwal penjemputan berkala untuk nasabah tetap.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 mb-4 shadow-sm" style="border-radius: 10px; background-color: #fee2e2; color: #b91c1c;">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>⚠️ {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0 p-4" style="border-radius: 16px; background: white; max-width: 600px;">
        <form action="{{ route('master-jadwal.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label Brief for="nasabah_id" class="form-label" style="font-weight: 600; color: #334155;">Nama Nasabah</label>
                <select class="form-select" id="nasabah_id" name="nasabah_id" required style="border-radius: 8px; padding: 10px; border: 1px solid #cbd5e1;">
                    <option value="" disabled selected>-- Pilih Nasabah --</option>
                    @foreach($nasabahs as $nasabah)
                        <option value="{{ $nasabah->id }}">
                            {{ $nasabah->name }} ({{ $nasabah->alamat ?? 'Alamat Belum Diisi' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="kurir_id" class="form-label" style="font-weight: 600; color: #334155;">Kurir Penanggung Jawab</label>
                <select class="form-select" id="kurir_id" name="kurir_id" required style="border-radius: 8px; padding: 10px; border: 1px solid #cbd5e1;">
                    <option value="" disabled selected>-- Pilih Kurir --</option>
                    @foreach($kurirs as $kurir)
                        <option value="{{ $kurir->id }}">{{ $kurir->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="hari_penjemputan" class="form-label" style="font-weight: 600; color: #334155;">Hari Penjemputan Rutin</label>
                <select class="form-select" id="hari_penjemputan" name="hari_penjemputan" required style="border-radius: 8px; padding: 10px; border: 1px solid #cbd5e1;">
                    <option value="" disabled selected>-- Pilih Hari --</option>
                    @foreach($hariOptions as $hari)
                        <option value="{{ $hari }}">{{ $hari }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label for="jam_estimasi" class="form-label" style="font-weight: 600; color: #334155;">Estimasi Jam Penjemputan</label>
                <input type="time" class="form-control" id="jam_estimasi" name="jam_estimasi" value="09:00" required style="border-radius: 8px; padding: 10px; border: 1px solid #cbd5e1;">
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" style="background-color: #16a34a; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600;">
                    💾 Simpan Pola Rutin
                </button>
                <a href="{{ route('master-jadwal.index') }}" class="btn btn-light" style="border-radius: 8px; padding: 10px 24px; font-weight: 600; border: 1px solid #cbd5e1; background-color: #f8fafc;">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection