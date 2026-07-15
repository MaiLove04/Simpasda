@extends('layouts.admin')

@section('content')
<div class="container-fluid px-2 py-2">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1" style="color: #0f172a; font-weight: bold;">✏️ Edit Jadwal Penjemputan</h1>
            <p class="text-muted mb-0" style="font-size: 14px;">Perbarui data plot penjemputan nasabah dan kurir penanggung jawab.</p>
        </div>
        <a href="{{ route('jadwal.index') }}" class="btn btn-outline-secondary" style="padding: 10px 18px; border-radius: 8px; font-weight: 500; border: 1px solid #cbd5e1; color: #475569; background: #ffffff;">
            ⬅️ Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 mb-4 shadow-sm" style="border-radius: 10px; background-color: #fee2e2; color: #b91c1c; font-weight: 500;">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>⚠️ {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0 p-4" style="border-radius: 16px; background: white;">
        <form action="{{ route('jadwal.update', $jadwal->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-md-6">
                    <label for="nasabah_id" class="form-label" style="font-weight: 600; color: #475569;">Nasabah</label>
                    <select name="nasabah_id" id="nasabah_id" class="form-select" style="border-radius: 8px; padding: 11px; border: 1px solid #cbd5e1;">
                        @foreach($nasabahs as $nasabah)
                            <option value="{{ $nasabah->id }}" {{ $jadwal->nasabah_id == $nasabah->id ? 'selected' : '' }}>
                                👤 {{ $nasabah->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="kurir_id" class="form-label" style="font-weight: 600; color: #475569;">Petugas Kurir</label>
                    <select name="kurir_id" id="kurir_id" class="form-select" style="border-radius: 8px; padding: 11px; border: 1px solid #cbd5e1;">
                        @foreach($kurirs as $kurir)
                            <option value="{{ $kurir->id }}" {{ $jadwal->kurir_id == $kurir->id ? 'selected' : '' }}>
                                🚚 {{ $kurir->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="tanggal_penjemputan" class="form-label" style="font-weight: 600; color: #475569;">Tanggal Penjemputan</label>
                    <input type="date" name="tanggal_penjemputan" id="tanggal_penjemputan" class="form-control" style="border-radius: 8px; padding: 11px; border: 1px solid #cbd5e1;" value="{{ \Carbon\Carbon::parse($jadwal->tanggal_penjemputan)->format('Y-m-d') }}">
                </div>

                <div class="col-md-6">
                    <label for="status" class="form-label" style="font-weight: 600; color: #475569;">Status Pengantaran</label>
                    <select name="status" id="status" class="form-select" style="border-radius: 8px; padding: 11px; border: 1px solid #cbd5e1;">
                        <option value="terjadwal" {{ strtolower($jadwal->status) == 'terjadwal' ? 'selected' : '' }}>🔵 Terjadwal</option>
                        <option value="proses" {{ strtolower($jadwal->status) == 'proses' ? 'selected' : '' }}>🟡 Proses</option>
                        <option value="selesai" {{ strtolower($jadwal->status) == 'selesai' ? 'selected' : '' }}>🟢 Selesai</option>
                        <option value="batal" {{ strtolower($jadwal->status) == 'batal' ? 'selected' : '' }}>🔴 Batal</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <label for="alamat" class="form-label" style="font-weight: 600; color: #475569;">Alamat Lengkap</label>
                    <textarea name="alamat" id="alamat" rows="2" class="form-control" style="border-radius: 8px; border: 1px solid #cbd5e1;" required>{{ $jadwal->alamat }}</textarea>
                </div>

                <div class="col-md-12">
                    <label for="catatan" class="form-label" style="font-weight: 600; color: #475569;">Catatan Lapangan (Opsional)</label>
                    <textarea name="catatan" id="catatan" rows="2" class="form-control" style="border-radius: 8px; border: 1px solid #cbd5e1;" placeholder="Tambahkan catatan khusus kurir lapangan...">{{ $jadwal->catatan }}</textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top: 1px solid #f1f5f9;">
                <button type="submit" class="btn btn-primary" style="background-color: #16a34a; border: none; padding: 11px 26px; border-radius: 8px; font-weight: 600;">
                    💾 Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection