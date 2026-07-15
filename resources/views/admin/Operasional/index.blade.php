    @extends('layouts.admin')

    @section('title', 'Data Operasional')

    @section('content')

    <div class="container-fluid py-3">

        {{-- ALERT --}}
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

                <h3 class="fw-bold mb-1">
                    <i class="fas fa-wallet text-success me-2"></i>
                    Data Operasional Bank Sampah
                </h3>

                <p class="text-muted mb-0">
                    Kelola seluruh pemasukan dan pengeluaran Bank Sampah.
                </p>

            </div>

            <div class="d-flex gap-2">

            <a href="{{ route('Operasional.exportPdf') }}"
            class="btn btn-danger">

                <i class="fas fa-file-pdf me-1"></i>
                Export PDF

            </a>

            <a href="{{ route('Operasional.create') }}"
            class="btn btn-primary">

                <i class="fas fa-plus-circle me-1"></i>
                Tambah Operasional

            </a>

        </div>

        </div>

        @php

            $totalPemasukan = 0;
            $totalPengeluaran = 0;

            foreach($operasional as $item){

                if($item->jenis_transaksi == 'Pemasukan'){
                    $totalPemasukan += $item->total;
                }else{
                    $totalPengeluaran += $item->total;
                }

            }

            $saldo = $totalPemasukan - $totalPengeluaran;

            $persenPemasukan =
                ($totalPemasukan+$totalPengeluaran)==0
                ?0:
                ($totalPemasukan/($totalPemasukan+$totalPengeluaran))*100;

            $persenPengeluaran =
                ($totalPemasukan+$totalPengeluaran)==0
                ?0:
                ($totalPengeluaran/($totalPemasukan+$totalPengeluaran))*100;

        @endphp

        {{-- CARD --}}
        <div class="row g-3 mb-4">

            <div class="col-md-4">

                <div class="card shadow border-0">

                    <div class="card-body">

                        <small class="text-muted">
                            Total Pemasukan
                        </small>

                        <h3 class="fw-bold text-success mt-2">

                            Rp {{ number_format($totalPemasukan,0,',','.') }}

                        </h3>

                    </div>

                </div>

            </div>

            <div class="col-md-4">

                <div class="card shadow border-0">

                    <div class="card-body">

                        <small class="text-muted">
                            Total Pengeluaran
                        </small>

                        <h3 class="fw-bold text-danger mt-2">

                            Rp {{ number_format($totalPengeluaran,0,',','.') }}

                        </h3>

                    </div>

                </div>

            </div>

            <div class="col-md-4">

                <div class="card shadow border-0">

                    <div class="card-body">

                        <small class="text-muted">
                            Saldo
                        </small>

                        <h3 class="fw-bold text-primary mt-2">

                            Rp {{ number_format($saldo,0,',','.') }}

                        </h3>

                    </div>

                </div>

            </div>

        </div>

        {{-- PROGRESS --}}
        <div class="card shadow border-0 mb-4">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <span class="fw-semibold text-success">

                        Pemasukan

                    </span>

                    <span>

                        {{ round($persenPemasukan) }}%

                    </span>

                </div>

                <div class="progress mb-3" style="height:10px;">

                    <div class="progress-bar bg-success"
                        style="width:{{ $persenPemasukan }}%">
                    </div>

                </div>

                <div class="d-flex justify-content-between">

                    <span class="fw-semibold text-danger">

                        Pengeluaran

                    </span>

                    <span>

                        {{ round($persenPengeluaran) }}%

                    </span>

                </div>

                <div class="progress" style="height:10px;">

                    <div class="progress-bar bg-danger"
                        style="width:{{ $persenPengeluaran }}%">
                    </div>

                </div>

            </div>

        </div>

        {{-- FILTER --}}
        <div class="card shadow border-0 mb-4">

            <div class="card-header bg-white">

                <form method="GET">

                    <div class="row g-2">

                        <div class="col-md-4">

                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                placeholder="Cari kategori..."
                                value="{{ request('search') }}">

                        </div>

                        <div class="col-md-3">

                            <input
                                type="month"
                                name="bulan"
                                class="form-control"
                                value="{{ request('bulan') }}">

                        </div>

                        <div class="col-md-3">

                            <button class="btn btn-primary">

                                Cari

                            </button>

                            <a href="{{ route('Operasional.index') }}"
                            class="btn btn-secondary">

                                Reset

                            </a>

                        </div>

                    </div>

                </form>

            </div>

        </div>

        {{-- TABEL --}}
        <div class="card shadow border-0">

            <div class="card-header bg-white">

                <h5 class="fw-bold mb-0">

                    Data Operasional

                </h5>

            </div>

            <div class="card-body table-responsive">

                <table class="table table-hover table-bordered align-middle">

                    <thead class="table-success">

                        <tr>

                            <th width="60">No</th>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Total</th>
                            <th>Keterangan</th>
                            <th>Sumber</th>
                            <th width="170">Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                    @forelse($operasional as $item)

    <tr>

        <td>

            {{ $operasional->firstItem() + $loop->index }}

        </td>

        <td>

            {{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}

        </td>

        <td>

            @if($item->jenis_transaksi == 'Pemasukan')

                <span class="badge bg-success">

                    Pemasukan

                </span>

            @else

                <span class="badge bg-danger">

                    Pengeluaran

                </span>

            @endif

        </td>

        <td>

            {{ $item->kategori }}

        </td>

        <td>

            Rp {{ number_format($item->harga,0,',','.') }}

        </td>

        <td>

            {{ $item->jumlah }}

        </td>

        <td>

            @if($item->jenis_transaksi=='Pemasukan')

                <span class="fw-bold text-success">

                    + Rp {{ number_format($item->total,0,',','.') }}

                </span>

            @else

                <span class="fw-bold text-danger">

                    - Rp {{ number_format($item->total,0,',','.') }}

                </span>

            @endif

        </td>

        <td>

            {{ $item->keterangan ?? '-' }}

        </td>

        <td>

            @if($item->sumber == 'Manual')

                <span class="badge bg-secondary">

                    Manual

                </span>

            @else

                <span class="badge bg-primary">

                    Otomatis

                </span>

            @endif

        </td>

        <td class="text-center">

            @if($item->sumber == 'Manual')

                <a href="{{ route('Operasional.edit',$item->id) }}"
                class="btn btn-warning btn-sm">

                    <i class="bi bi-pencil-square"></i>

                </a>

                <form action="{{ route('Operasional.destroy',$item->id) }}"
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

            @else

                <span class="text-muted">

                    -

                </span>

            @endif

        </td>

    </tr>

    @empty

    <tr>

        <td colspan="10" class="text-center py-4 text-muted">

            Belum ada data operasional.

        </td>

    </tr>

    @endforelse

    </tbody>
                <tfoot>

                    <tr class="table-light">

                        <th colspan="6" class="text-end">

                            Total Pemasukan

                        </th>

                        <th class="text-success">

                            Rp {{ number_format($totalPemasukan,0,',','.') }}

                        </th>

                        <th colspan="3"></th>

                    </tr>

                    <tr class="table-light">

                        <th colspan="6" class="text-end">

                            Total Pengeluaran

                        </th>

                        <th class="text-danger">

                            Rp {{ number_format($totalPengeluaran,0,',','.') }}

                        </th>

                        <th colspan="3"></th>

                    </tr>

                    <tr class="table-warning">

                        <th colspan="6" class="text-end">

                            Saldo

                        </th>

                        <th class="text-primary">

                            Rp {{ number_format($saldo,0,',','.') }}

                        </th>

                        <th colspan="3"></th>

                    </tr>

                </tfoot>

            </table>

                {{ $operasional->links('pagination::bootstrap-5') }}

        </div>

    </div>

</div>

 @endsection