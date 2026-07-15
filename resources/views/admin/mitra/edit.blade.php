@extends('layouts.admin')

@section('title', 'Edit Mitra')

@section('content')

<div class="container-fluid py-3">

    <div class="mb-4">

        <h3 class="fw-bold">

            <i class="bi bi-pencil-square text-warning me-2"></i>

            Edit Mitra

        </h3>

        <p class="text-muted mb-0">

            Perbarui data mitra Bank Sampah.

        </p>

    </div>

    <div class="card shadow border-0">

        <div class="card-body">

            <form action="{{ route('Mitra.update', $mitra->id) }}" method="POST">

                @csrf
                @method('PUT')

                <div class="row">

                    {{-- Nama Mitra --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">

                            Nama Mitra

                        </label>

                        <input
                            type="text"
                            name="nama_mitra"
                            class="form-control @error('nama_mitra') is-invalid @enderror"
                            value="{{ old('nama_mitra', $mitra->nama_mitra) }}"
                            placeholder="Masukkan nama mitra">

                        @error('nama_mitra')

                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>

                        @enderror

                    </div>

                    {{-- Jenis Mitra --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">

                            Jenis Mitra

                        </label>

                        <select
                            name="jenis_mitra"
                            class="form-select @error('jenis_mitra') is-invalid @enderror">

                            <option value="">-- Pilih Jenis Mitra --</option>

                            <option value="Pengepul"
                                {{ old('jenis_mitra', $mitra->jenis_mitra) == 'Pengepul' ? 'selected' : '' }}>
                                Pengepul
                            </option>

                            <option value="Vendor"
                                {{ old('jenis_mitra', $mitra->jenis_mitra) == 'Vendor' ? 'selected' : '' }}>
                                Vendor
                            </option>

                            <option value="Instansi"
                                {{ old('jenis_mitra', $mitra->jenis_mitra) == 'Instansi' ? 'selected' : '' }}>
                                Instansi
                            </option>

                            <option value="UMKM"
                                {{ old('jenis_mitra', $mitra->jenis_mitra) == 'UMKM' ? 'selected' : '' }}>
                                UMKM
                            </option>

                            <option value="Lainnya"
                                {{ old('jenis_mitra', $mitra->jenis_mitra) == 'Lainnya' ? 'selected' : '' }}>
                                Lainnya
                            </option>

                        </select>

                        @error('jenis_mitra')

                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>

                        @enderror

                    </div>

                    {{-- Penanggung Jawab --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">

                            Penanggung Jawab

                        </label>

                        <input
                            type="text"
                            name="penanggung_jawab"
                            class="form-control @error('penanggung_jawab') is-invalid @enderror"
                            value="{{ old('penanggung_jawab', $mitra->penanggung_jawab) }}"
                            placeholder="Masukkan nama penanggung jawab">

                        @error('penanggung_jawab')

                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>

                        @enderror

                    </div>

                    {{-- No HP --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">

                            Nomor HP

                        </label>

                        <input
                            type="text"
                            name="no_hp"
                            class="form-control @error('no_hp') is-invalid @enderror"
                            value="{{ old('no_hp', $mitra->no_hp) }}"
                            placeholder="Contoh: 081234567890">

                        @error('no_hp')

                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>

                        @enderror

                    </div>

                    {{-- Email --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">

                            Email

                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $mitra->email) }}"
                            placeholder="contoh@email.com">

                        @error('email')

                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>

                        @enderror

                    </div>

                    {{-- Status --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">

                            Status

                        </label>

                        <select
                            name="status"
                            class="form-select @error('status') is-invalid @enderror">

                            <option value="Aktif"
                                {{ old('status', $mitra->status) == 'Aktif' ? 'selected' : '' }}>

                                Aktif

                            </option>

                            <option value="Tidak Aktif"
                                {{ old('status', $mitra->status) == 'Tidak Aktif' ? 'selected' : '' }}>

                                Tidak Aktif

                            </option>

                        </select>

                        @error('status')

                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>

                        @enderror

                    </div>

                    {{-- Alamat --}}
                    <div class="col-md-12 mb-3">

                        <label class="form-label fw-semibold">

                            Alamat

                        </label>

                        <textarea
                            name="alamat"
                            rows="3"
                            class="form-control @error('alamat') is-invalid @enderror"
                            placeholder="Masukkan alamat lengkap mitra">{{ old('alamat', $mitra->alamat) }}</textarea>

                        @error('alamat')

                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>

                        @enderror

                    </div>

                    {{-- Keterangan --}}
                    <div class="col-md-12 mb-4">

                        <label class="form-label fw-semibold">

                            Keterangan

                        </label>

                        <textarea
                            name="keterangan"
                            rows="3"
                            class="form-control @error('keterangan') is-invalid @enderror"
                            placeholder="Keterangan (opsional)">{{ old('keterangan', $mitra->keterangan) }}</textarea>

                        @error('keterangan')

                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>

                        @enderror

                    </div>

                    {{-- Tombol --}}
                    <div class="col-12">

                        <a href="{{ route('Mitra.index') }}"
                           class="btn btn-secondary">

                            <i class="bi bi-arrow-left"></i>

                            Kembali

                        </a>

                        <button
                            type="submit"
                            class="btn btn-warning">

                            <i class="bi bi-save"></i>

                            Update

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection