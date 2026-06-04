@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <a href="{{ route('admin.tarik-tunai.index') }}" class="text-decoration-none fw-bold" style="color: #1E521E;">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Nasabah
        </a>
        <h2 class="fw-bold text-dark mt-2 mb-1">Formulir Tarik Tunai</h2>
        <p class="text-muted">Proses pencairan saldo tunai atas nama nasabah secara instan.</p>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
            <i class="fas fa-exclamation-circle me-2"></i><strong>{{ session('error') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0" style="color: #1E521E;">Input Nominal Penarikan</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <form action="{{ url('/admin/tarik-tunai/' . $nasabah->id) }}" method="POST">
                        @csrf

                        <div class="p-3 mb-4 bg-light d-flex justify-content-between align-items-center" style="border-radius: 12px;">
                            <div>
                                <span class="text-muted small d-block">Nama Nasabah</span>
                                <strong class="text-dark text-uppercase">{{ $nasabah->name }}</strong>
                            </div>
                            <div class="text-end">
                                <span class="text-muted small d-block">Saldo Berjalan</span>
                                <strong class="text-success" style="font-size: 16px;">Rp {{ number_format($nasabah->saldo, 0, ',', '.') }}</strong>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="nominal" class="form-label fw-bold text-dark">Jumlah yang Ingin Ditarik</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold text-dark" style="border-radius: 10px 0 0 10px;">Rp</span>
                                <input type="number" class="form-control @error('nominal') is-invalid @enderror" 
                                       name="nominal" id="nominal" placeholder="Contoh: 25000" min="1" max="{{ $nasabah->saldo }}" required 
                                       style="border-radius: 0 10px 10px 0; font-size: 16px; font-weight: bold;">
                            </div>
                            @error('nominal')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-muted small mt-2">Pastikan nominal tidak melebihi sisa saldo berjalan nasabah.</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-lg text-white fw-bold py-2" 
                                    style="background-color: #1E521E; border-radius: 12px; font-size: 16px;"
                                    onclick="return confirm('Apakah Anda yakin ingin memproses penarikan tunai untuk nasabah ini?')">
                                <i class="fas fa-check-circle me-2"></i> Konfirmasi & Serahkan Uang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-5 ms-md-3">
            <div class="p-4 bg-light" style="border-radius: 16px; border-left: 5px solid #1E521E;">
                <h6 class="fw-bold text-dark mb-3"><i class="fas fa-shield-alt text-success me-2"></i> Prosedur Keamanan Kasir</h6>
                <ol class="text-muted small ps-3">
                    <li class="mb-2">Mintalah nasabah menyebutkan nama atau menunjukkan kartu member/QR code untuk validasi kecocokan data fisik.</li>
                    <li class="mb-2">Input nominal penarikan sesuai jumlah uang fisik yang diminta dan akan diserahkan.</li>
                    <li class="mb-2">Setelah menekan tombol konfirmasi, sistem akan otomatis menerbitkan record di tabel **`MutasiSaldo`** dengan status **`success`** dan jenis **`keluar`**.</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection