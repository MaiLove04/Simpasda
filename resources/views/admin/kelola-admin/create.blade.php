@extends('layouts.admin')

@section('content')

<div class="container-fluid px-3 py-3">

    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <div>
            <h1 class="h3 fw-bold">Tambah Admin</h1>
            <p class="text-muted mb-0">
                Tambahkan administrator baru untuk mengelola Bank Sampah.
            </p>
        </div>

        <a href="{{ route('kelola-admin.index') }}"
            class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Kembali
        </a>
    </div>

    <div class="card shadow-sm border-0">

        <div class="card-body">

            <form action="{{ route('kelola-admin.store') }}" method="POST">

                @csrf

                <div class="mb-3">
                    <label class="form-label">Nama Admin</label>

                    <input
                        type="text"
                        name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}"
                        required>

                    @error('name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        required>

                    @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        No HP
                    </label>

                    <input
                        type="text"
                        name="no_hp"
                        class="form-control"
                        value="{{ old('no_hp') }}">

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Alamat
                    </label>

                    <textarea
                        name="alamat"
                        rows="3"
                        class="form-control">{{ old('alamat') }}</textarea>

                </div>

                <div class="row">

                    <div class="col-md-6">

                        <label class="form-label">
                            Password
                        </label>

                        <input
                            type="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            required>

                        @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="col-md-6">

                        <label class="form-label">
                            Konfirmasi Password
                        </label>

                        <input
                            type="password"
                            name="password_confirmation"
                            class="form-control"
                            required>

                    </div>

                </div>

                <hr>

                <button class="btn btn-success">

                    <i class="fas fa-save"></i>

                    Simpan

                </button>

            </form>

        </div>

    </div>

</div>

@endsection
