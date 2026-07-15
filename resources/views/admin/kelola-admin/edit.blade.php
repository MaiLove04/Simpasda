@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-success text-white p-3">
            <div class="d-flex align-items-center">
                <i class="bi bi-person-gear me-2 fs-4"></i> <!-- Contoh jika pakai Bootstrap Icons -->
                <h5 class="mb-0 fw-bold">Detail Administrator</h5>
            </div>
        </div>

        <div class="card-body p-4">
            <form action="{{ route('kelola-admin.update', $admin->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Data Utama (Dibuat 2 Kolom pada Layar Desktop) -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" value="{{ $admin->name }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary">Alamat Email</label>
                        <input type="email" name="email" class="form-control" value="{{ $admin->email }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary">No. Handphone</label>
                        <input type="text" name="no_hp" class="form-control" value="{{ $admin->no_hp }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="1">{{ $admin->alamat }}</textarea>
                    </div>
                </div>

                <div class="my-4 text-muted">
                    <hr>
                </div>

                <!-- Bagian Ubah Password -->
                <h6 class="fw-bold text-success mb-3">
                    <i class="bi bi-shield-lock me-1"></i> Ganti Password
                </h6>
                <p class="text-muted small mb-3">Kosongkan jika tidak ingin mengubah password.</p>

                <div class="row g-3">
                    <!-- Password Baru -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary">Password Baru</label>
                        <div class="input-group">
                            <input type="password" id="password" name="password" class="form-control">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Konfirmasi Password -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary">Konfirmasi Password</label>
                        <div class="input-group">
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmation">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="d-flex justify-content-end gap-2 mt-4 pt-2 border-top">
                    <a href="{{ route('kelola-admin.index') }}" class="btn btn-light px-4 border">
                        Kembali
                    </a>
                    <button type="submit" class="btn btn-success px-4">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script untuk Fitur Lihat Password -->
<script>
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                // Jika pakai FontAwesome ganti 'bi-eye' / 'bi-eye-slash' jadi 'fa-eye' / 'fa-eye-slash'
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });
</script>
@endsection