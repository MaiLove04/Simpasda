@extends('layouts.admin')

@section('content')

<style>

    body{
        background: #f5f7fb;
    }

    .card{
        border-radius: 20px;
    }

    .table thead th{
        font-size: 14px;
        font-weight: 600;
        color: #6c757d;
        background: #f8f9fc;
        border: none;
        padding: 18px;
    }

    .table td{
        vertical-align: middle;
        border-color: #f1f1f1;
        padding: 18px;
    }

    .badge{
        border-radius: 10px;
        font-weight: 500;
        padding: 8px 14px;
    }

    .btn{
        border-radius: 10px;
    }

    .search-box{
        width: 300px;
    }

    .stats-card h6{
        font-size: 15px;
        color: #6c757d;
        margin-bottom: 12px;
    }

    .stats-card h2{
        font-weight: 700;
        font-size: 40px;
    }

    .main-card{
        border-radius: 24px;
    }

</style>

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="fw-bold mb-2">
                Jadwal Kurir
            </h1>

            <p class="text-muted mb-0">
                Kelola dan atur jadwal penjemputan sampah oleh kurir.
            </p>

        </div>


        <a href="/admin/jadwal/create"
            class="btn btn-primary px-4 py-3">

            <i class="bi bi-plus-lg"></i>
            Tambah Jadwal

        </a>

    </div>


    <!-- Statistik -->
    <div class="row mb-4">

        <!-- Total -->
        <div class="col-md-3 mb-3">

            <div class="card border-0 shadow-sm stats-card h-100">

                <div class="card-body">

                    <h6>Total Jadwal</h6>

                    <h2>
                        {{ $jadwals->count() }}
                    </h2>

                </div>

            </div>

        </div>


        <!-- Menunggu -->
        <div class="col-md-3 mb-3">

            <div class="card border-0 shadow-sm stats-card h-100">

                <div class="card-body">

                    <h6>Menunggu</h6>

                    <h2 class="text-warning">

                        {{ $jadwals->where('status','menunggu')->count() }}

                    </h2>

                </div>

            </div>

        </div>


        <!-- Diproses -->
        <div class="col-md-3 mb-3">

            <div class="card border-0 shadow-sm stats-card h-100">

                <div class="card-body">

                    <h6>Diproses</h6>

                    <h2 class="text-primary">

                        {{ $jadwals->where('status','diproses')->count() }}

                    </h2>

                </div>

            </div>

        </div>


        <!-- Selesai -->
        <div class="col-md-3 mb-3">

            <div class="card border-0 shadow-sm stats-card h-100">

                <div class="card-body">

                    <h6>Selesai</h6>

                    <h2 class="text-success">

                        {{ $jadwals->where('status','selesai')->count() }}

                    </h2>

                </div>

            </div>

        </div>

    </div>


    <!-- Main Table -->
    <div class="card border-0 shadow-sm main-card">

        <div class="card-body p-4">

            <!-- Header Table -->
            <div class="d-flex justify-content-between align-items-center mb-4">

                <h3 class="fw-semibold mb-0">

                    Daftar Jadwal

                </h3>


                <div class="search-box">

                    <input
                        type="text"
                        class="form-control"
                        placeholder="Cari nasabah atau kurir..."
                    >

                </div>

            </div>


            <!-- Table -->
            <div class="table-responsive">

                <table class="table align-middle">

                    <thead>

                        <tr>

                            <th>No</th>

                            <th>Nasabah</th>

                            <th>Kurir</th>

                            <th>Alamat</th>

                            <th>Tanggal</th>

                            <th>Status</th>

                            <th class="text-center">
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($jadwals as $index => $jadwal)

                        <tr>

                            <!-- No -->
                            <td>

                                {{ $index + 1 }}

                            </td>


                            <!-- Nasabah -->
                            <td>

                                <div class="fw-semibold">

                                    {{ $jadwal->nasabah->name }}

                                </div>

                            </td>


                            <!-- Kurir -->
                            <td>

                                {{ $jadwal->kurir->name }}

                            </td>


                            <!-- Alamat -->
                            <td>

                                {{ $jadwal->alamat }}

                            </td>


                            <!-- Tanggal -->
                            <td>

                                {{ \Carbon\Carbon::parse($jadwal->tanggal_penjemputan)->format('d M Y') }}

                            </td>


                            <!-- Status -->
                            <td>

                                @if($jadwal->status == 'menunggu')

                                    <span class="badge bg-warning-subtle text-warning">

                                        Menunggu

                                    </span>

                                @elseif($jadwal->status == 'diproses')

                                    <span class="badge bg-primary-subtle text-primary">

                                        Diproses

                                    </span>

                                @elseif($jadwal->status == 'selesai')

                                    <span class="badge bg-success-subtle text-success">

                                        Selesai

                                    </span>

                                @else

                                    <span class="badge bg-secondary-subtle text-secondary">

                                        {{ $jadwal->status }}

                                    </span>

                                @endif

                            </td>


                            <!-- Aksi -->
                            <td class="text-center">

                                <div class="d-flex justify-content-center gap-2">

                                    <!-- Detail -->
                                    <a href="#"
                                        class="btn btn-light border btn-sm">

                                        <i class="bi bi-eye"></i>

                                    </a>


                                    <!-- Edit -->
                                    <a href="/admin/jadwal/{{ $jadwal->id }}/edit"
                                        class="btn btn-light border btn-sm">

                                        <i class="bi bi-pencil"></i>

                                    </a>


                                    <!-- Hapus -->
                                    <form
                                        action="/admin/jadwal/{{ $jadwal->id }}"
                                        method="POST"
                                    >

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            class="btn btn-light border btn-sm text-danger"
                                            onclick="return confirm('Yakin hapus jadwal ini?')"
                                        >

                                            <i class="bi bi-trash"></i>

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="7"
                                class="text-center text-muted py-5">

                                Data jadwal belum tersedia

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection