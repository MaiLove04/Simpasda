@extends('partner.layouts.app')

@section('title','Pengiriman Saya')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">📦 Pengiriman Saya</h2>
            <p class="text-muted mb-0">
                Daftar seluruh pengiriman yang telah dibuat.
            </p>
        </div>
    </div>

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white fw-bold">
            Daftar Pengiriman
        </div>

        <div class="card-body table-responsive">

            <table class="table table-hover align-middle">

                <thead class="table-success">

                    <tr>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Total Berat</th>
                        <th>Total Pembayaran</th>
                        <th>Status Pengiriman</th>
                        <th>Status Pembayaran</th>
                        <th width="120">Aksi</th>
                    </tr>

                </thead>

                <tbody>

                @forelse($pengiriman as $item)

                    <tr>

                        <td>
                            <strong>{{ $item->kode_pengiriman }}</strong>
                        </td>

                        <td>
                            {{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}
                        </td>

                        <td>
                            {{ $item->details->sum('berat') }} Kg
                        </td>

                        <td>
                            Rp {{ number_format($item->total,0,',','.') }}
                        </td>

                        <td>

                            @if($item->status_pengiriman == 'Menunggu Mitra')
                                <span class="badge bg-warning text-dark">
                                    {{ $item->status_pengiriman }}
                                </span>

                            @elseif($item->status_pengiriman == 'Diterima')
                                <span class="badge bg-success">
                                    {{ $item->status_pengiriman }}
                                </span>

                            @else
                                <span class="badge bg-secondary">
                                    {{ $item->status_pengiriman }}
                                </span>
                            @endif

                        </td>

                        <td>

                            @if($item->status_pembayaran == 'Belum Bayar')

                                <span class="badge bg-danger">
                                    {{ $item->status_pembayaran }}
                                </span>

                            @elseif($item->status_pembayaran == 'Menunggu Verifikasi')

                                <span class="badge bg-warning text-dark">
                                    {{ $item->status_pembayaran }}
                                </span>

                            @elseif($item->status_pembayaran == 'Lunas')

                                <span class="badge bg-success">
                                    {{ $item->status_pembayaran }}
                                </span>

                            @else

                                <span class="badge bg-secondary">
                                    {{ $item->status_pembayaran }}
                                </span>

                            @endif

                        </td>

                        <td>

                            <a href="{{ route('pengiriman.show',$item->id) }}"
                               class="btn btn-success btn-sm">

                                <i class="bi bi-eye"></i>

                                Detail

                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="7" class="text-center py-4">

                            <div class="text-muted">

                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>

                                Belum ada data pengiriman.

                            </div>

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        @if($pengiriman->hasPages())

        <div class="card-footer bg-white">

            {{ $pengiriman->links() }}

        </div>

        @endif

    </div>

</div>

@endsection