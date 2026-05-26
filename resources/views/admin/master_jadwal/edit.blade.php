@extends('layouts.admin')

@section('content')
<div class="container-fluid px-2 py-2">
    <div class="mb-4">
        <h1 class="h2 mb-1" style="color: #0f172a; font-weight: bold;">Edit Pola Rutin</h1>
        <p class="text-muted mb-0" style="font-size: 14px;">Ubah aturan atau status jadwal penjemputan berkala nasabah.</p>
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
        <form action="{{ route('master-jadwal.update', $master->id) }}" method="POST">
            @csrf
            @method('PUT') <div class="mb-3">
                <label for="nasabah_id" class="form-label" style="font-weight: 600; color: #334155;">Nama Nasabah</label>
                <select class="form-select" id="nasabah_id" name="nasabah_id" required style="border-radius: 8px; padding: 10px; border: 1px solid #cbd5e1;">
                    @foreach($nasabahs as $nasabah)
                        <option value="{{ $nasabah->id }}" {{ $master->nasabah_id == $nasabah->id ? 'selected' : '' }}>
                            {{ $nasabah->name }} ({{ $nasabah->alamat ?? 'Alamat Belum Diisi' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="kurir_id" class="form-label" style="font-weight: 600; color: #334155;">Kurir Penanggung Jawab</label>
                <select class="form-select" id="kurir_id" name="kurir_id" required style="border-radius: 8px; padding: 10px; border: 1px solid #cbd5e1;">
                    @foreach($kurirs as $kurir)
                        <option value="{{ $kurir->id }}" {{ $master->kurir_id == $kurir->id ? 'selected' : '' }}>
                            {{ $kurir->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="hari_penjemputan" class="form-label" style="font-weight: 600; color: #334155;">Hari Penjemputan Rutin</label>
                <select class="form-select" id="hari_penjemputan" name="hari_penjemputan" required style="border-radius: 8px; padding: 10px; border: 1px solid #cbd5e1;">
                    @foreach($hariOptions as $hari)
                        <option value="{{ $hari }}" {{ $master->hari_penjemputan == $hari ? 'selected' : '' }}>
                            {{ $hari }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="jam_estimasi" class="form-label" style="font-weight: 600; color: #334155;">Estimasi Jam Penjemputan</label>
                <input type="time" class="form-control" id="jam_estimasi" name="jam_estimasi" value="{{ \Carbon\Carbon::parse($master->jam_estimasi)->format('H:i') }}" required style="border-radius: 8px; padding: 10px; border: 1px solid #cbd5e1;">
            </div>

            <div class="mb-4 form-check form-switch p-0 ps-5">
                <input class="form-check-input" type="checkbox" id="is_aktif" name="is_aktif" value="1" {{ $master->is_aktif ? 'checked' : '' }} style="transform: scale(1.2); cursor: pointer;">
                <label class="form-check-label ms-2" for="is_aktif" style="font-weight: 600; color: #334155; cursor: pointer;">Pola Rutin Ini Aktif</label>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" style="background-color: #16a34a; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600;">
                    💾 Perbarui Pola
                </button>
                <a href="{{ route('master-jadwal.index') }}" class="btn btn-light" style="border-radius: 8px; padding: 10px 24px; font-weight: 600; border: 1px solid #cbd5e1; background-color: #f8fafc;">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection