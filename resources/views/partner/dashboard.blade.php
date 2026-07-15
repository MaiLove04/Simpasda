@extends('partner.layouts.app')

@section('title','Dashboard')

@section('content')

<div class="mb-4">

    <h2 class="fw-bold">

        Halo, {{ auth()->user()->name }} 👋

    </h2>

    <p class="text-muted">

        Selamat datang di Portal Partner SIMPASDA.

    </p>

</div>

<div class="row g-3">

    <div class="col-md-3">

        <div class="card shadow-sm border-0">

            <div class="card-body">

                <small class="text-muted">

                    Menunggu Diterima

                </small>

                <h2 class="fw-bold text-warning mt-2">

                    {{ $menunggu }}

                </h2>

            </div>

        </div>

    </div>

    <div class="col-md-3">

        <div class="card shadow-sm border-0">

            <div class="card-body">

                <small class="text-muted">

                    Sudah Diterima

                </small>

                <h2 class="fw-bold text-success mt-2">

                    {{ $diterima }}

                </h2>

            </div>

        </div>

    </div>

    <div class="col-md-3">

        <div class="card shadow-sm border-0">

            <div class="card-body">

                <small class="text-muted">

                    Belum Dibayar

                </small>

                <h2 class="fw-bold text-danger mt-2">

                    {{ $belumBayar }}

                </h2>

            </div>

        </div>

    </div>

    <div class="col-md-3">

        <div class="card shadow-sm border-0">

            <div class="card-body">

                <small class="text-muted">

                    Lunas

                </small>

                <h2 class="fw-bold text-primary mt-2">

                    {{ $lunas }}

                </h2>

            </div>

        </div>

    </div>

</div>

<div class="card shadow-sm border-0 mt-4">

    <div class="card-header bg-white">

        <h5 class="fw-bold mb-0">

            Pengiriman Terbaru

        </h5>

    </div>

    <div class="card-body table-responsive">

        <table class="table table-hover">

            <thead>

                <tr>

                    <th>Kode</th>
                    <th>Tanggal</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th></th>

                </tr>

            </thead>

            <tbody>

                @forelse($pengiriman as $item)

                <tr>

                    <td>{{ $item->kode_pengiriman }}</td>

                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}</td>

                    <td>

                        Rp {{ number_format($item->total,0,',','.') }}

                    </td>

                    <td>

                        {{ $item->status_pengiriman }}

                    </td>

                    <td>

                        <a href="{{ route('pengiriman.show', $item->id) }}"
                        class="btn btn-success btn-sm">
                            Detail
                        </a>

                    </td>

                </tr>

                @empty

                <tr>

                    <td colspan="5" class="text-center">

                        Belum ada pengiriman.

                    </td>

                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection