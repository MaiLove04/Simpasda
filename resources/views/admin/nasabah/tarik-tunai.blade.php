@extends('layouts.app') <!-- Sesuaikan dengan nama layout dashboard admin Anda, misal: layouts.admin -->

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h1 class="h3 mb-2 text-gray-800">Tarik Tunai Saldo Nasabah</h1>
            <p class="text-muted">Proses penarikan saldo tabungan sampah nasabah oleh admin.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Tarik Tunai</h6>
                </div>
                <div class="card-body">
                    <!-- Menampilkan Pesan Error (Misal: Saldo Tidak Cukup) -->
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Informasi Saldo Nasabah -->
                    <div class="alert alert-info">
                        <strong>Nama Nasabah:</strong> {{ $nasabah->name }} <br>
                        <strong>Kode Nasabah:</strong> {{ $nasabah->kode_nasabah ?? '-' }} <br>
                        <strong>Saldo Saat Ini:</strong> <span class="badge bg-success" style="font-size: 1.1em;">Rp {{ number_format($nasabah->saldo, 0, ',', '.') }}</span>
                    </div>

                    <!-- Form Penarikan -->
                    <form action="{{ route('admin.nasabah.proses-tarik-tunai', $nasabah->id) }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="nominal" class="form-label">Nominal Penarikan (Rp) <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('nominal') is-invalid @enderror" 
                                   id="nominal" 
                                   name="nominal" 
                                   min="1" 
                                   max="{{ $nasabah->saldo }}"
                                   placeholder="Contoh: 50000" 
                                   value="{{ old('nominal') }}"
                                   required>
                            
                            @error('nominal')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">Maksimal penarikan adalah sesuai jumlah saldo nasabah saat ini.</small>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('admin.nasabah.index') }}" class="btn btn-secondary me-2">Batal</a>
                            <button type="submit" class="btn btn-primary" onclick="return confirm('Apakah Anda yakin ingin memproses penarikan uang sejumlah ini?')">Proses Tarik Tunai</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection