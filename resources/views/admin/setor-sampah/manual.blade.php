@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    
    <!-- Header Halaman -->
    <div class="mb-4">
        <a href="/admin/setor-sampah" class="text-decoration-none fw-bold text-success" style="color: #16a34a;">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
        <h2 class="fw-bold text-dark mt-2 mb-1">
            <i class="bi bi-box-seam-fill text-success me-2"></i>Loket Setor Sampah Manual
        </h2>
        <p class="text-muted">Timbang dan input sampah yang dibawa langsung oleh nasabah ke kantor.</p>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px; background-color: #fee2e2; color: #991b1b;">
            <i class="bi bi-exclamation-circle-fill me-2"></i><strong>{{ session('error') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Form Kiri -->
        <div class="col-md-7">
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <form action="{{ route('admin.setor.proses-manual', $nasabah->id) }}" method="POST">
                        @csrf

                        <!-- Profil Singkat Nasabah -->
                        <div class="p-3 mb-4 bg-light d-flex justify-content-between align-items-center" style="border-radius: 12px; border-left: 4px solid #16a34a;">
                            <div>
                                <span class="text-muted small d-block">Nama Nasabah</span>
                                <strong class="text-dark fs-5">{{ $nasabah->name }}</strong>
                                <span class="text-secondary small d-block">ID: {{ $nasabah->kode_nasabah ?? '-' }}</span>
                            </div>
                            <div class="text-end">
                                <span class="text-muted small d-block">Saldo Saat Ini</span>
                                <strong class="text-success fs-5">Beta Rp {{ number_format($nasabah->saldo, 0, ',', '.') }}</strong>
                            </div>
                        </div>

                        <!-- Pilih Jenis Sampah -->
                        <div class="mb-3">
                            <label for="jenis_sampah_id" class="form-label fw-bold text-dark">Pilih Jenis Sampah</label>
                            <select class="form-select py-2.5 @error('jenis_sampah_id') is-invalid @enderror" 
                                    name="jenis_sampah_id" id="jenis_sampah_id" required style="border-radius: 10px;">
                                <option value="" selected disabled>-- Pilih Kategori Sampah --</option>
                                @foreach($jenisSampah as $sampah)
                                    <option value="{{ $sampah->id }}" data-harga="{{ $sampah->harga_per_kg }}">
                                        {{ $sampah->nama_sampah }} (Rp {{ number_format($sampah->harga_per_kg, 0, ',', '.') }}/kg)
                                    </option>
                                @endforeach
                            </select>
                            @error('jenis_sampah_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Input Berat Sampah -->
                        <div class="mb-4">
                            <label for="berat" class="form-label fw-bold text-dark">Berat Timbangan Sampah</label>
                            <div class="input-group">
                                <input type="number" step="0.1" class="form-control py-2.5 @error('berat') is-invalid @enderror" 
                                       name="berat" id="berat" placeholder="Contoh: 2.5" min="0.1" required 
                                       style="border-radius: 10px 0 0 10px; font-weight: 500;">
                                <span class="input-group-text bg-light fw-bold text-dark" style="border-radius: 0 10px 10px 0;">Kg</span>
                            </div>
                            @error('berat')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Kalkulator Otomatis Real-Time (Live Preview) -->
                        <div class="p-3 mb-4 border border-dashed text-dark" style="border-radius: 12px; background-color: #f8fafc; border-style: dashed !important;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small">Estimasi Pendapatan:</span>
                                <span class="text-muted small" id="kalkulasi-detail">0 kg x Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <strong class="text-dark">Total Tambah Saldo:</strong>
                                <strong class="text-success fs-4" id="total-live">Rp 0</strong>
                            </div>
                        </div>

                        <!-- Tombol Submit -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg fw-bold py-2.5" 
                                    style="background-color: #16a34a; border: none; border-radius: 12px; font-size: 16px;"
                                    onclick="return confirm('Apakah timbangan sudah pas dan jenis sampah sesuai?')">
                                <i class="bi bi-plus-circle me-2"></i> Timbang & Cetak Saldo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panduan Kanan (Biar Bagus & Seimbang) -->
        <div class="col-md-5">
            <div class="card shadow-sm border-0 bg-dark text-white" style="border-radius: 16px; background-color: #0f172a !important;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-info-circle text-success me-2"></i>Prosedur Setoran Loket</h5>
                    <ul class="text-white-50 small ps-3 mb-0" style="line-height: 1.8;">
                        <li class="mb-2">Pastikan sampah sudah dipilah dan dibersihkan dari zat cair berbahaya.</li>
                        <li class="mb-2">Pilih kategori sampah yang sesuai pada menu dropdown agar kalkulasi harga per kilogram akurat.</li>
                        <li class="mb-2">Gunakan alat timbangan digital kantor untuk mendapatkan nilai berat sampah yang valid.</li>
                        <li>Setelah tombol ditekan, saldo nasabah akan bertambah secara otomatis di database SIMPASDA.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script Live Kalkulator -->
<script>
    const selectSampah = document.getElementById('jenis_sampah_id');
    const inputBerat = document.getElementById('berat');
    const txtDetail = document.getElementById('kalkulasi-detail');
    const txtTotal = document.getElementById('total-live');

    function hitungOtomatis() {
        const berat = parseFloat(inputBerat.value) || 0;
        const selectedOption = selectSampah.options[selectSampah.selectedIndex];
        const harga = selectedOption ? parseFloat(selectedOption.getAttribute('data-harga')) || 0 : 0;

        const total = berat * harga;

        // Format Rupiah
        const formatter = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        });

        txtDetail.textContent = `${berat} kg x ${formatter.format(harga)}`;
        txtTotal.textContent = formatter.format(total);
    }

    selectSampah.addEventListener('change', hitungOtomatis);
    inputBerat.addEventListener('input', hitungOtomatis);
</script>
@endsection