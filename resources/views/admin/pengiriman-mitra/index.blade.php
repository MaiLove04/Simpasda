@extends('layouts.admin')

@section('title', 'Pengiriman Mitra')

@section('content')

<div class="container-fluid py-3">

        @if(session('success'))

    <div class="alert alert-success alert-dismissible fade show">

        {{ session('success') }}

        <button class="btn-close" data-bs-dismiss="alert"></button>

    </div>

    @endif

    @if(session('error'))

    <div class="alert alert-danger alert-dismissible fade show">

        {{ session('error') }}

        <button class="btn-close" data-bs-dismiss="alert"></button>

    </div>

    @endif

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3 class="fw-bold">
                📦 Pengiriman Mitra
            </h3>
            <p class="text-muted mb-0">
                Kelola semua pengiriman sampah ke mitra
            </p>
        </div>

        <a href="{{ route('pengiriman-mitra.create') }}" class="btn btn-success">
            + Buat Pengiriman
        </a>

    </div>

    <div class="row mb-4">

        <div class="col-md-3">

            <div class="card shadow border-0">

                <div class="card-body">

                    <small>Total Pengiriman</small>

                    <h3 class="fw-bold">

                        {{ $totalPengiriman }}

                    </h3>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card shadow border-0">

                <div class="card-body">

                    <small>Menunggu Mitra</small>

                    <h3 class="fw-bold text-warning">

                        {{ $menunggu }}

                    </h3>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card shadow border-0">

                <div class="card-body">

                    <small>Diterima</small>

                    <h3 class="fw-bold text-info">

                        {{ $diterima }}

                    </h3>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card shadow border-0">

                <div class="card-body">

                    <small>Lunas</small>

                    <h3 class="fw-bold text-success">

                        {{ $lunas }}

                    </h3>

                </div>

            </div>

        </div>

    </div>

    <div class="card shadow border-0 mb-4">

        <div class="card-body">

            <form method="GET">

                <div class="row">

                    <div class="col-md-3">

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Cari Mitra / Kode"
                            value="{{ request('search') }}">

                    </div>

                    <div class="col-md-2">

                        <select
                            name="status_pengiriman"
                            class="form-select">

                            <option value="">Status Pengiriman</option>

                            <option value="Menunggu Mitra">Menunggu</option>

                            <option value="Diterima">Diterima</option>

                            <option value="Selesai">Selesai</option>

                        </select>

                    </div>

                    <div class="col-md-2">

                        <select
                            name="status_pembayaran"
                            class="form-select">

                            <option value="">Status Bayar</option>

                            <option value="Belum Bayar">Belum Bayar</option>

                            <option value="Menunggu Verifikasi">Verifikasi</option>

                            <option value="Lunas">Lunas</option>

                        </select>

                    </div>

                    <div class="col-md-2">

                        <input
                            type="month"
                            name="bulan"
                            class="form-control"
                            value="{{ request('bulan') }}">

                    </div>

                    <div class="col-md-3">

                        <button class="btn btn-success">

                            Cari

                        </button>

                        <a href="{{ route('pengiriman-mitra.index') }}"
                            class="btn btn-secondary">

                            Reset

                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>

    {{-- TABLE --}}
    <div class="card shadow border-0">
        <div class="card-body table-responsive">

            <table class="table table-hover align-middle">

                <thead class="table-success">
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Mitra</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Status Kirim</th>
                        <th>Status Bayar</th>
                        <th width="170">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($data as $item)

                    <tr>

                        <td>
                            {{ $data->firstItem() + $loop->index }}
                        </td>

                        <td>
                            <span class="fw-bold text-primary">
                                {{ $item->kode_pengiriman }}
                            </span>
                        </td>

                        <td>
                            {{ $item->mitra->nama_mitra }}
                        </td>

                        <td>
                            {{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}
                        </td>

                        <td>
                            Rp {{ number_format($item->total,0,',','.') }}
                        </td>

                        {{-- STATUS KIRIM --}}
                        <td>
                            @if($item->status_pengiriman == 'Menunggu Mitra')
                                <span class="badge bg-warning">Menunggu</span>
                            @elseif($item->status_pengiriman == 'Diterima')
                                <span class="badge bg-info">Diterima</span>
                            @else
                                <span class="badge bg-success">Selesai</span>
                            @endif
                        </td>

                        {{-- STATUS BAYAR --}}
                        <td>
                            @if($item->status_pembayaran == 'Belum Bayar')
                                <span class="badge bg-danger">Belum</span>
                            @elseif($item->status_pembayaran == 'Menunggu Verifikasi')
                                <span class="badge bg-warning">Verifikasi</span>
                            @else
                                <span class="badge bg-success">Lunas</span>
                            @endif
                        </td>

                        {{-- ACTION --}}
                        <td>

                            <a href="{{ route('pengiriman-mitra.show',$item->id) }}"
                            class="btn btn-info btn-sm">

                                <i class="bi bi-eye"></i>

                            </a>

                            @if($item->status_pengiriman == 'Menunggu Mitra')

                                <a href="{{ route('pengiriman-mitra.edit',$item->id) }}"
                                class="btn btn-warning btn-sm">

                                    <i class="bi bi-pencil-square"></i>

                                </a>

                                <form action="{{ route('pengiriman-mitra.destroy',$item->id) }}"
                                    method="POST"
                                    class="d-inline">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Yakin ingin menghapus pengiriman ini?')">

                                        <i class="bi bi-trash"></i>

                                    </button>

                                </form>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            Belum ada data pengiriman
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top bg-light"
             style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
        
            <div class="text-muted" style="font-size: 12px;">
                Menampilkan {{ $data->firstItem() ?? 0 }}-{{ $data->lastItem() ?? 0 }}
                dari {{ $data->total() }} data pengiriman
            </div>
        
            <div class="sm-pagination">
                {{ $data->appends(request()->input())->links('pagination::bootstrap-5') }}
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