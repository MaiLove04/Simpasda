@extends('layouts.admin') 
@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">

    <div>
        <a href="/admin/setor-sampah"
           class="text-decoration-none fw-bold text-success">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>

        <h2 class="fw-bold text-dark mt-2 mb-1">
            <i class="bi bi-box-seam-fill text-success me-2"></i>
            Loket Setor Sampah Manual
        </h2>

        <p class="text-muted mb-0">
            Timbang dan input sampah yang dibawa langsung oleh nasabah ke kantor.
        </p>
    </div>

    <div class="text-end">
        <span class="badge bg-success fs-6 px-3 py-2">
            <i class="bi bi-shop me-1"></i> Setor Manual
        </span>
    </div>

</div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-calculator me-1"></i> Input Timbangan Loket
        </div>
        <div class="card-body">
            <form action="{{ route('admin.setor.store-manual') }}" method="POST">
                @csrf

                <div class="mb-4 col-md-6">
                    <label for="nasabah_id" class="form-label fw-bold">Pilih Nasabah</label>
                    <select name="nasabah_id" id="nasabah_id" class="form-select @error('nasabah_id') is-invalid @enderror" required>
                        <option value="" selected disabled>-- Cari / Pilih Nasabah --</option>
                        @foreach($dataNasabah as $nasabah)
                            <option value="{{ $nasabah->id }}" {{ old('nasabah_id') == $nasabah->id ? 'selected' : '' }}>
                                {{ $nasabah->name }} (Saldo: Rp {{ number_format($nasabah->saldo, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                    @error('nasabah_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <h5 class="fw-bold border-bottom pb-2">Daftar Item Timbangan</h5>
                <div id="wrapper-item">
                    <div class="row g-3 align-items-end item-sampah-row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Jenis Sampah</label>
                            <select name="items[0][jenis_sampah_id]" class="form-select select-jenis" required>
                                <option value="" selected disabled>-- Pilih Jenis --</option>
                                @foreach($jenisSampah as $sampah)
                                    <option value="{{ $sampah->id }}" data-harga="{{ $sampah->harga_per_kg }}">
                                        {{ $sampah->nama_sampah ?? $sampah->nama }} (Rp {{ number_format($sampah->harga_per_kg, 0, ',', '.') }}/kg)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Berat (Kg)</label>
                            <div class="input-group">
                                <input type="number" step="0.1" min="0.1" name="items[0][berat]" class="form-control input-berat" placeholder="0.0" required>
                                <button type="button" class="btn btn-secondary btn-ambil-iot"><i class="fas fa-balance-scale"></i> IoT</button>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger w-100 btn-hapus-row" disabled><i class="fas fa-trash"></i> Hapus</button>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="button" id="btn-tambah-item" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Baris Sampah
                    </button>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('admin.setor.index') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-success px-4"><i class="fas fa-save"></i> Simpan & Isi Saldo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let itemIndex = 1;
        const wrapper = document.getElementById('wrapper-item');
        const btnTambah = document.getElementById('btn-tambah-item');

        // Fungsi Tambah Baris Baru
        btnTambah.addEventListener('click', function() {
            const firstRow = document.querySelector('.item-sampah-row').cloneNode(true);
            
            // Reset input nilai di baris baru
            firstRow.querySelector('.select-jenis').name = `items[${itemIndex}][jenis_sampah_id]`;
            firstRow.querySelector('.select-jenis').selectedIndex = 0;
            
            const beratInput = firstRow.querySelector('.input-berat');
            beratInput.name = `items[${itemIndex}][berat]`;
            beratInput.value = '';

            // Aktifkan tombol hapus pada baris baru
            const btnHapus = firstRow.querySelector('.btn-hapus-row');
            btnHapus.removeAttribute('disabled');

            wrapper.appendChild(firstRow);
            itemIndex++;
        });

        // Event Handler delegasi untuk Hapus Baris & Hitung Timbangan IoT
        wrapper.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-hapus-row') || e.target.closest('.btn-hapus-row')) {
                const row = e.target.closest('.item-sampah-row');
                row.remove();
            }

            // Integrasi Simulasi Berat IoT Ajax
            if (e.target.classList.contains('btn-ambil-iot') || e.target.closest('.btn-ambil-iot')) {
                const btn = e.target.closest('.btn-ambil-iot');
                const row = btn.closest('.item-sampah-row');
                const inputBerat = row.querySelector('.input-berat');

                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                fetch("{{ route('admin.setor.index') }}/../get-berat-iot") // Menembak method getBeratIot() di controller
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            inputBerat.value = data.berat_iot;
                        }
                    })
                    .catch(err => alert('Gagal membaca sensor timbangan.'))
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-balance-scale"></i> IoT';
                    });
            }
        });
    });
</script>
@endsection