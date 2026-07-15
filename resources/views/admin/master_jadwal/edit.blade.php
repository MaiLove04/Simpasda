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
            @method('PUT')

            <div class="mb-3">
                <label for="nasabah_id" class="form-label" style="font-weight: 600; color: #334155;">Nama Nasabah</label>
                <select class="form-select" id="nasabah_id" name="nasabah_id" required style="border-radius: 8px; padding: 10px; border: 1px solid #cbd5e1;">
                    @foreach($nasabahs as $nasabah)
                        <option value="{{ $nasabah->id }}" {{ (old('nasabah_id', $master->nasabah_id) == $nasabah->id) ? 'selected' : '' }}>
                            {{ $nasabah->name }} ({{ $nasabah->alamat ?? 'Alamat Belum Diisi' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="kurir_id" class="form-label" style="font-weight: 600; color: #334155;">Kurir Penanggung Jawab</label>
                <select class="form-select" id="kurir_id" name="kurir_id" required style="border-radius: 8px; padding: 10px; border: 1px solid #cbd5e1;">
                    @foreach($kurirs as $kurir)
                        <option value="{{ $kurir->id }}" {{ (old('kurir_id', $master->kurir_id) == $kurir->id) ? 'selected' : '' }}>
                            {{ $kurir->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ====== TIPE JADWAL ====== --}}
            <div class="mb-3">
                <label class="form-label" style="font-weight: 600; color: #334155;">Tipe Jadwal</label>
                <div class="d-flex gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tipe_jadwal" id="tipe_mingguan" value="mingguan" {{ old('tipe_jadwal', $master->tipe_jadwal) === 'mingguan' ? 'checked' : '' }} onchange="toggleTipeJadwal()">
                        <label class="form-check-label" for="tipe_mingguan" style="font-weight: 500; color: #334155;">📅 Mingguan</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tipe_jadwal" id="tipe_interval" value="interval" {{ old('tipe_jadwal', $master->tipe_jadwal) === 'interval' ? 'checked' : '' }} onchange="toggleTipeJadwal()">
                        <label class="form-check-label" for="tipe_interval" style="font-weight: 500; color: #334155;">🔄 Interval Hari</label>
                    </div>
                </div>
                <small class="text-muted">Mingguan = pilih hari tetap. Interval = setiap N hari sekali.</small>
            </div>

            {{-- ====== FIELD MINGGUAN ====== --}}
            <div class="mb-3" id="field-mingguan">
                <label for="hari_penjemputan" class="form-label" style="font-weight: 600; color: #334155;">Hari Penjemputan Rutin</label>
                <select class="form-select" id="hari_penjemputan" name="hari_penjemputan" style="border-radius: 8px; padding: 10px; border: 1px solid #cbd5e1;">
                    <option value="" disabled>-- Pilih Hari --</option>
                    @foreach($hariOptions as $hari)
                        <option value="{{ $hari }}" {{ old('hari_penjemputan', $master->hari_penjemputan) === $hari ? 'selected' : '' }}>
                            {{ $hari }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ====== FIELD INTERVAL ====== --}}
            <div id="field-interval" style="display: none;">
                <div class="mb-3">
                    <label for="interval_hari" class="form-label" style="font-weight: 600; color: #334155;">Setiap Berapa Hari?</label>
                    <div class="input-group" style="max-width: 250px;">
                        <span class="input-group-text" style="border-radius: 8px 0 0 8px; background-color: #f8fafc; border: 1px solid #cbd5e1;">Setiap</span>
                        <input type="number" class="form-control" id="interval_hari" name="interval_hari" min="2" max="30" value="{{ old('interval_hari', $master->interval_hari ?? 2) }}" style="border: 1px solid #cbd5e1; text-align: center;">
                        <span class="input-group-text" style="border-radius: 0 8px 8px 0; background-color: #f8fafc; border: 1px solid #cbd5e1;">hari sekali</span>
                    </div>
                    <small class="text-muted">Minimal 2 hari, maksimal 30 hari.</small>
                </div>
                <div class="mb-3">
                    <label for="tanggal_mulai" class="form-label" style="font-weight: 600; color: #334155;">Tanggal Mulai</label>
                    <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', $master->tanggal_mulai ? $master->tanggal_mulai->format('Y-m-d') : date('Y-m-d')) }}" style="border-radius: 8px; padding: 10px; border: 1px solid #cbd5e1; max-width: 250px;">
                    <small class="text-muted">Tanggal pertama penjemputan dimulai.</small>
                </div>
            </div>

            <div class="mb-3">
                <label for="jam_estimasi" class="form-label" style="font-weight: 600; color: #334155;">Estimasi Jam Penjemputan</label>
                <input type="time" class="form-control" id="jam_estimasi" name="jam_estimasi" value="{{ old('jam_estimasi', \Carbon\Carbon::parse($master->jam_estimasi)->format('H:i')) }}" required style="border-radius: 8px; padding: 10px; border: 1px solid #cbd5e1;">
            </div>

            <div class="mb-4 form-check form-switch p-0 ps-5">
                <input class="form-check-input" type="checkbox" id="is_aktif" name="is_aktif" value="1" {{ old('is_aktif', $master->is_aktif) ? 'checked' : '' }} style="transform: scale(1.2); cursor: pointer;">
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

<script>
    function toggleTipeJadwal() {
        const tipe = document.querySelector('input[name="tipe_jadwal"]:checked').value;
        const fieldMingguan = document.getElementById('field-mingguan');
        const fieldInterval = document.getElementById('field-interval');

        if (tipe === 'mingguan') {
            fieldMingguan.style.display = 'block';
            fieldInterval.style.display = 'none';
            document.getElementById('hari_penjemputan').setAttribute('required', '');
            document.getElementById('interval_hari').removeAttribute('required');
            document.getElementById('tanggal_mulai').removeAttribute('required');
        } else {
            fieldMingguan.style.display = 'none';
            fieldInterval.style.display = 'block';
            document.getElementById('hari_penjemputan').removeAttribute('required');
            document.getElementById('interval_hari').setAttribute('required', '');
            document.getElementById('tanggal_mulai').setAttribute('required', '');
        }
    }

    // Inisialisasi saat halaman dimuat
    document.addEventListener('DOMContentLoaded', toggleTipeJadwal);
</script>
@endsection