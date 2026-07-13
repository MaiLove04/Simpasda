@extends('partner.layouts.app')

@section('title','Riwayat Pembayaran')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">
                <i class="bi bi-receipt"></i>
                Riwayat Pembayaran
            </h2>
            <p class="text-muted mb-0">
                Daftar seluruh pembayaran pengiriman sampah.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white fw-bold">
            Data Pembayaran
        </div>

        <div class="card-body table-responsive">

            <table class="table table-hover align-middle">

                <thead class="table-success">

                    <tr>
                        <th>Kode Pengiriman</th>
                        <th>Tanggal Bayar</th>
                        <th>Metode</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Bukti</th>
                    </tr>

                </thead>

                <tbody>

                @forelse($pembayaran as $item)

                    <tr>

                        <td>
                            <strong>{{ $item->kode_pengiriman }}</strong>
                        </td>

                        <td>

                            @if($item->tanggal_pembayaran)

                                {{ \Carbon\Carbon::parse($item->tanggal_pembayaran)->format('d M Y') }}

                            @else

                                -

                            @endif

                        </td>

                        <td>

                            {{ $item->metode_pembayaran ?? '-' }}

                        </td>

                        <td>

                            Rp {{ number_format($item->total,0,',','.') }}

                        </td>

                        <td>

                            @if($item->status_pembayaran=='Belum Bayar')

                                <span class="badge bg-danger">
                                    Belum Bayar
                                </span>

                            @elseif($item->status_pembayaran=='Menunggu Verifikasi')

                                <span class="badge bg-warning text-dark">
                                    Menunggu Verifikasi
                                </span>

                            @elseif($item->status_pembayaran=='Lunas')

                                <span class="badge bg-success">
                                    Lunas
                                </span>

                            @else

                                <span class="badge bg-secondary">
                                    {{ $item->status_pembayaran }}
                                </span>

                            @endif

                        </td>

                        <td>

                            @if($item->bukti_transfer)

                                <a href="{{ asset('storage/'.$item->bukti_transfer) }}"
                                   target="_blank"
                                   class="btn btn-outline-primary btn-sm">

                                    <i class="bi bi-image"></i>
                                    Lihat

                                </a>

                            @else

                                <span class="text-muted">
                                    -
                                </span>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="6" class="text-center py-5">

                            <i class="bi bi-receipt fs-1 text-muted"></i>

                            <p class="mt-3 text-muted mb-0">

                                Belum ada riwayat pembayaran.

                            </p>

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        @if($pembayaran->hasPages())

        <div class="card-footer bg-white">

            {{ $pembayaran->links() }}

        </div>

        @endif

    </div>

</div>

@endsection