@extends('layouts.admin')

@section('content')
<div class="container-fluid px-3 py-3">
    <div class="d-flex align-items-center mb-4 border-bottom pb-3">
        <a href="{{ route('admin.tarik-tunai.index') }}" class="btn btn-sm btn-light border me-3" style="border-radius: 8px;">
            <i class="fas fa-arrow-left text-muted"></i>
        </a>
        <div>
            <h1 class="h3 mb-1" style="color: #0f172a; font-weight: bold;">Tarik Tunai Saldo Nasabah</h1>
            <p class="text-muted mb-0" style="font-size: 13px;">Proses penarikan saldo tabungan sampah nasabah oleh admin.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-header py-3 bg-white border-bottom" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h6 class="m-0 font-weight-bold" style="color: #1E521E;">Form Tarik Tunai</h6>
                </div>
                <div class="card-body p-4">
                    <!-- Menampilkan Pesan Error -->
                    @if(session('error'))
                        <div class="alert alert-danger" style="border-radius: 8px; font-size: 14px;">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Informasi Saldo Nasabah -->
                    <div class="alert mb-4" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted" style="font-size: 13px;">Nama Nasabah:</span>
                            <span style="font-weight: 600; color: #0f172a; font-size: 14px;">{{ $nasabah->name }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted" style="font-size: 13px;">Kode Nasabah:</span>
                            <span style="font-weight: 600; color: #0f172a; font-size: 14px;">{{ $nasabah->kode_nasabah ?? '-' }}</span>
                        </div>
                        <hr class="my-2 text-muted">
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span class="text-muted" style="font-weight: 600; font-size: 14px;">Saldo Saat Ini:</span>
                            <span class="badge" style="background-color: #dcfce7; color: #16a34a; font-size: 16px; padding: 8px 12px; border: 1px solid #bbf7d0;">
                                Rp {{ number_format($nasabah->saldo, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <!-- Form Penarikan -->
                    <form action="{{ route('admin.tarik-tunai.proses', $nasabah->id) }}" method="POST">
                        @csrf
                        <div class="form-group mb-4">
                            <label for="nominal" class="form-label" style="font-weight: 600; font-size: 14px; color: #334155;">
                                Nominal Penarikan (Rp) <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   class="form-control form-control-lg @error('nominal') is-invalid @enderror" 
                                   id="nominal" 
                                   name="nominal" 
                                   min="1" 
                                   max="{{ $nasabah->saldo }}"
                                   placeholder="Contoh: 50000" 
                                   value="{{ old('nominal') }}"
                                   style="border-radius: 8px; font-size: 15px;"
                                   required>
                            
                            @error('nominal')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted mt-2 d-block" style="font-size: 12px;">
                                <i class="fas fa-info-circle me-1"></i> Maksimal penarikan adalah sesuai jumlah saldo saat ini.
                            </small>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-2">
                            <a href="{{ route('admin.tarik-tunai.index') }}" class="btn btn-light border px-4" style="border-radius: 8px; font-weight: 600; font-size: 14px;">Batal</a>
                            <button type="submit" class="btn text-white px-4" style="background-color: #1E521E; border-radius: 8px; font-weight: 600; font-size: 14px;" onclick="return confirm('Apakah Anda yakin ingin memproses penarikan uang ini?')">Proses Penarikan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection