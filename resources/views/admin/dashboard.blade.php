@extends('layouts.admin')

@section('content')
<div class="container-fluid px-2 py-2">

    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="h2 mb-1" style="color: #0f172a; font-weight: bold;">Dashboard Admin</h1>
            <p class="text-muted mb-0" style="font-size: 14px;">Selamat datang kembali, {{ auth()->user()->name }} Kelola operasional hari ini.</p>
        </div>

        {{-- Ganti action dari "/admin/logout" menjadi "{{ route('logout') }}" --}}
        <form method="POST" action="{{ route('logout') }}" onsubmit="return confirm('Yakin ingin keluar dari panel admin?')">
            @csrf
            <button class="btn btn-outline-danger d-flex align-items-center gap-2" type="submit" style="border-radius: 8px; padding: 10px 16px; font-weight: 600;">
                <i class="bi bi-box-arrow-right"></i> Keluar Aplikasi
            </button>
        </form>
    </div>

    <div class="row g-4 mb-5">

        <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 16px; background: white;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 text-uppercase tracking-wider" style="font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">Total Nasabah</p>
                        <h3 class="mb-0 fw-bold" style="color: #0f172a;">{{ $totalNasabah ?? '124' }}</h3>
                    </div>
                    <div class="p-3 rounded-3" style="background: #e0f2fe; color: #0369a1;">
                        <i class="bi bi-people-fill fs-4" style="line-height: 1;"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-success small fw-semibold"><i class="bi bi-arrow-up-short"></i> +12%</span>
                    <span class="text-muted small ms-1">bulan ini</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 16px; background: white;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 text-uppercase tracking-wider" style="font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">Kurir Aktif</p>
                        <h3 class="mb-0 fw-bold" style="color: #0f172a;">{{ $totalKurir ?? '8' }}</h3>
                    </div>
                    <div class="p-3 rounded-3" style="background: #fef3c7; color: #b45309;">
                        <i class="bi bi-truck fs-4" style="line-height: 1;"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-muted small">Semua armada siap jalan</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 16px; background: white;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 text-uppercase tracking-wider" style="font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">Setoran Hari Ini</p>
                        <h3 class="mb-0 fw-bold" style="color: #0f172a;">{{ $beratHariIni ?? '45' }} Kg</h3>
                    </div>
                    <div class="p-3 rounded-3" style="background: #dcfce7; color: #16a34a;">
                        <i class="bi bi-trash3-fill fs-4" style="line-height: 1;"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-success small fw-semibold"><i class="bi bi-arrow-up-short"></i> +5 Kg</span>
                    <span class="text-muted small ms-1">dari kemarin</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 16px; background: white;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 text-uppercase tracking-wider" style="font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">Sisa Antrean Rute</p>
                        <h3 class="mb-0 fw-bold" style="color: #0f172a;">{{ $jadwalPending ?? '3' }} Lokasi</h3>
                    </div>
                    <div class="p-3 rounded-3" style="background: #fee2e2; color: #b91c1c;">
                        <i class="bi bi-clock-history fs-4" style="line-height: 1;"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-muted small">Perlu dipantau berkala</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 16px; background: white; border-left: 5px solid #16a34a;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 text-uppercase tracking-wider" style="font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">Request Tarik Tunai</p>
                        <h3 class="mb-0 fw-bold" style="color: #16a34a;">{{ $tarikPending ?? '0' }} Antrean</h3>
                    </div>
                    <div class="p-3 rounded-3" style="background: #dcfce7; color: #16a34a;">
                        <i class="bi bi-cash-coin fs-4" style="line-height: 1;"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.tarik-tunai.index') }}" class="text-success small fw-bold text-decoration-none">Lihat & Approve <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>

    </div>

    <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 16px; background: white;">
        <h4 class="h5 mb-3 fw-bold" style="color: #0f172a;">Aktivitas Operasional Instan</h4>
        <p class="text-muted small mb-4">Gunakan pintasan di bawah ini untuk mengelola penjadwalan berkala nasabah secara cepat tanpa membuka menu sidebar:</p>

        <div class="row g-3">
            <div class="col-md-6">
                <a href="{{ route('master-jadwal.create') }}" class="btn btn-light w-100 text-start p-3 d-flex align-items-center justify-content-between border" style="border-radius: 12px; transition: all 0.2s;">
                    <div class="d-flex align-items-center">
                        <div class="p-2 rounded bg-success text-white me-3"><i class="bi bi-calendar-plus"></i></div>
                        <div>
                            <div class="fw-bold text-dark" style="font-size: 14px;">Plot Jadwal Rutin Baru</div>
                            <small class="text-muted" style="font-size: 12px;">Daftarkan pelanggan tetap mingguan</small>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>
            </div>
            <div class="col-md-6">
                <a href="/admin/jadwal" class="btn btn-light w-100 text-start p-3 d-flex align-items-center justify-content-between border" style="border-radius: 12px; transition: all 0.2s;">
                    <div class="d-flex align-items-center">
                        <div class="p-2 rounded bg-primary text-white me-3"><i class="bi bi-eye"></i></div>
                        <div>
                            <div class="fw-bold text-dark" style="font-size: 14px;">Lihat Antrean Hari Ini</div>
                            <small class="text-muted" style="font-size: 12px;">Pantau rute jalan kurir di lapangan</small>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
