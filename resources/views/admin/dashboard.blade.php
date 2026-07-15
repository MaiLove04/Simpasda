@extends('layouts.admin')

@section('content')

<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-success mb-1">
                Dashboard Admin
            </h2>
            <p class="text-muted mb-0">
                Selamat datang kembali,
                <strong>{{ auth()->user()->name }}</strong>.
                Berikut ringkasan aktivitas SIMPASDA hari ini.
            </p>
        </div>

        <div class="d-flex gap-2">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="btn btn-outline-danger">
                    <i class="bi bi-box-arrow-right"></i>
                    Logout
                </button>
            </form>
        </div>
    </div>

    {{-- Statistik Baris 1 --}}
    <div class="row g-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted">Total Nasabah</small>
                            <h2 class="fw-bold mt-2">{{ $totalNasabah }}</h2>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-people-fill fs-2 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted">Total Sampah Terkonfirmasi</small>
                            <div class="d-flex align-items-baseline gap-1 mt-2">
                                <h2 class="fw-bold m-0">{{ number_format($totalSampah, 1, ',', '.') }}</h2>
                                <span class="text-xs text-muted fw-semibold">Kg</span>
                            </div>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-recycle fs-2 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted">Total Kurir</small>
                            <h2 class="fw-bold mt-2">{{ $totalKurir }}</h2>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-truck fs-2 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted">Total Partner</small>
                            <h2 class="fw-bold mt-2">{{ $totalPartner }}</h2>
                        </div>
                        <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-building fs-2 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistik Baris 2 --}}
    <div class="row g-4 mt-1">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted">Jadwal Hari Ini</small>
                    <h2 class="fw-bold mt-2 text-primary">{{ $jadwalHariIni }}</h2>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted">Menunggu Konfirmasi</small>
                    <h2 class="fw-bold mt-2 text-warning">{{ $pengirimanMenunggu }}</h2>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted">Menunggu Verifikasi</small>
                    <h2 class="fw-bold mt-2 text-danger">{{ $verifikasiPembayaran }}</h2>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted">Pembayaran Lunas</small>
                    <h2 class="fw-bold mt-2 text-success">{{ $pembayaranLunas }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4 g-4 align-items-start">

    {{-- Request Tarik Tunai --}}
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm"
            style="border-radius:16px; border-left:5px solid #16a34a;">
            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-uppercase fw-bold text-muted">
                            Request Tarik Tunai
                        </small>

                        <h2 class="fw-bold text-success mt-2 mb-0">
                            {{ $tarikPending ?? 0 }}
                        </h2>

                        <span class="fs-5 fw-semibold text-success">
                            Antrean
                        </span>
                    </div>

                    <div class="bg-success bg-opacity-10 rounded-3 p-3">
                        <i class="bi bi-cash-coin fs-3 text-success"></i>
                    </div>
                </div>

                <hr>

                <a href="{{ route('admin.tarik-tunai.index') }}"
                    class="text-success fw-semibold text-decoration-none">
                    Lihat & Approve
                    <i class="bi bi-arrow-right"></i>
                </a>

            </div>
        </div>
    </div>

    {{-- Grafik --}}
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h4 class="fw-bold mb-0">
                    <i class="bi bi-graph-up-arrow text-success"></i>
                    Statistik Setoran Sampah (2026)
                </h4>
            </div>

            <div class="card-body">
                <canvas id="grafikSetoran" height="110"></canvas>
            </div>
        </div>
    </div>

</div>

<div class="row mt-4">
    <div class="col-12">
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
    </div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('grafikSetoran');

new Chart(ctx, {
    type: 'line', 
    data: {
        // Menggunakan variabel bulanData dari controller
        labels: {!! json_encode($bulanData) !!},
        datasets: [{
            label: 'Total Sampah Dihasilkan (Kg)', 
            // Menggunakan variabel jumlahSetoranData dari controller
            data: {!! json_encode($jumlahSetoranData) !!},
            borderColor: '#198754', 
            backgroundColor: 'rgba(25,135,84,.15)',
            fill: true,
            tension: .4,
            borderWidth: 3,
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true 
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Berat (Kg)' 
                }
            }
        }
    }
});
</script>

@endsection