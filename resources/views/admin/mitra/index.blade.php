@extends('layouts.admin')

@section('title', 'Data Mitra')

@section('content')

<div class="container-fluid py-3">

    {{-- Alert --}}
    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show">

            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"></button>

        </div>

    @endif

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold mb-1">

                <i class="bi bi-people-fill text-success me-2"></i>

                Data Mitra

            </h3>

            <p class="text-muted mb-0">

                Kelola seluruh mitra kerja Bank Sampah.

            </p>

        </div>

        <a href="{{ route('Mitra.create') }}"
           class="btn btn-success">

            <i class="bi bi-plus-circle"></i>

            Tambah Mitra

        </a>

    </div>

    {{-- Card Statistik --}}
    <div class="row g-3 mb-4">

        <div class="col-md-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <small class="text-muted">

                        Total Mitra

                    </small>

                    <h3 class="fw-bold mt-2">

                        {{ $totalMitra }}

                    </h3>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <small class="text-muted">

                        Mitra Aktif

                    </small>

                    <h3 class="fw-bold text-success mt-2">

                        {{ $mitraAktif }}

                    </h3>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <small class="text-muted">

                        Tidak Aktif

                    </small>

                    <h3 class="fw-bold text-danger mt-2">

                        {{ $mitraTidakAktif }}

                    </h3>

                </div>

            </div>

        </div>

    </div>
        {{-- Filter --}}
    <div class="card shadow border-0 mb-4">

        <div class="card-body">

            <form method="GET">

                <div class="row g-3">

                    <div class="col-md-5">

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Cari nama mitra..."
                            value="{{ request('search') }}">

                    </div>

                    <div class="col-md-3">

                        <select
                            name="status"
                            class="form-select">

                            <option value="">

                                Semua Status

                            </option>

                            <option
                                value="Aktif"
                                {{ request('status')=='Aktif' ? 'selected' : '' }}>

                                Aktif

                            </option>

                            <option
                                value="Tidak Aktif"
                                {{ request('status')=='Tidak Aktif' ? 'selected' : '' }}>

                                Tidak Aktif

                            </option>

                        </select>

                    </div>

                    <div class="col-md-4">

                        <button
                            class="btn btn-success">

                            <i class="bi bi-search"></i>

                            Cari

                        </button>

                        <a
                            href="{{ route('Mitra.index') }}"
                            class="btn btn-secondary">

                            <i class="bi bi-arrow-clockwise"></i>

                            Reset

                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>

    {{-- Tabel --}}
    <div class="card shadow border-0">

        <div class="card-header bg-white">

            <h5 class="fw-bold mb-0">

                Daftar Mitra

            </h5>

        </div>

        <div class="card-body table-responsive">

            <table class="table table-hover table-bordered align-middle">

                <thead class="table-success">

                    <tr>

                        <th width="60">No</th>

                        <th>Nama Mitra</th>

                        <th>Jenis</th>

                        <th>Penanggung Jawab</th>

                        <th>No HP</th>

                        <th>Status</th>

                        <th width="150" class="text-center">

                            Aksi

                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($mitra as $item)

<tr>

    <td>

        {{ $mitra->firstItem() + $loop->index }}

    </td>

    <td>

        <strong>{{ $item->nama_mitra }}</strong>

    </td>

    <td>

        <span class="badge bg-primary">

            {{ $item->jenis_mitra }}

        </span>

    </td>

    <td>

        {{ $item->penanggung_jawab }}

    </td>

    <td>

        {{ $item->no_hp }}

    </td>

    <td>

        @if($item->status == 'Aktif')

            <span class="badge bg-success">

                Aktif

            </span>

        @else

            <span class="badge bg-danger">

                Tidak Aktif

            </span>

        @endif

    </td>

    <td class="text-center">

        <a href="{{ route('Mitra.show',$item->id) }}"
        class="btn btn-info btn-sm">

            <i class="bi bi-eye"></i>

        </a>

        <a href="{{ route('Mitra.edit',$item->id) }}"
        class="btn btn-warning btn-sm">

            <i class="bi bi-pencil-square"></i>

        </a>

        <form action="{{ route('Mitra.destroy',$item->id) }}"
            method="POST"
            class="d-inline">

            @csrf
            @method('DELETE')

            <button
                class="btn btn-danger btn-sm"
                onclick="return confirm('Yakin ingin menghapus data ini?')">

                <i class="bi bi-trash"></i>

            </button>

        </form>

    </td>

</tr>

@empty

<tr>

    <td colspan="7" class="text-center py-4 text-muted">

        Belum ada data mitra.

    </td>

</tr>

@endforelse

</tbody>

</table>

    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top bg-light"
     style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">

    <div class="text-muted" style="font-size: 12px;">
        Menampilkan {{ $mitra->firstItem() ?? 0 }}-{{ $mitra->lastItem() ?? 0 }}
        dari {{ $mitra->total() }} data mitra
    </div>

    <div class="sm-pagination">
        {{ $mitra->appends(request()->input())->links('pagination::bootstrap-5') }}
    </div>

</div>

</div>

</div>

</div>
<style>
    .sm-pagination .pagination{
        margin-bottom: 0;
        gap: 2px;
    }

    .sm-pagination .page-link{
        padding: 4px 10px;
        font-size: 12px;
        border-radius: 4px;
    }
</style>

@endsection