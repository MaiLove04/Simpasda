@extends('layouts.admin')

@section('title', 'Detail Pengiriman')

@section('content')

<div class="container-fluid py-3">

    {{-- HEADER --}}
    <div class="mb-4">
        <h3 class="fw-bold">
            📦 Detail Pengiriman {{ $data->kode_pengiriman }}
        </h3>
        <p class="text-muted">
            Mitra: {{ $data->mitra->nama_mitra }}
        </p>
    </div>

    {{-- INFO --}}
    <div class="card mb-3">
        <div class="card-body row">

            <div class="col-md-3">
                <small class="text-muted">Tanggal</small>
                <div class="fw-bold">
                    {{ \Carbon\Carbon::parse($data->tanggal)->format('d M Y') }}
                </div>
            </div>

            <div class="col-md-3">
                <small class="text-muted">Total</small>
                <div class="fw-bold text-success">
                    Rp {{ number_format($data->total,0,',','.') }}
                </div>
            </div>

            <div class="col-md-3">
                <small class="text-muted">Status Kirim</small><br>
                <span class="badge bg-info">
                    {{ $data->status_pengiriman }}
                </span>
            </div>

            <div class="col-md-3">
                <small class="text-muted">Status Bayar</small><br>
                <span class="badge bg-warning">
                    {{ $data->status_pembayaran }}
                </span>
            </div>

        </div>
    </div>

    {{-- DETAIL BARANG --}}
    <div class="card mb-3">
        <div class="card-header bg-white fw-bold">
            Detail Sampah
        </div>

        <div class="card-body table-responsive">

            <table class="table table-bordered">

                <thead class="table-success">
                    <tr>
                        <th>Jenis</th>
                        <th>Berat</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($data->details as $d)
                        <tr>
                            <td>{{ $d->jenis_sampah }}</td>
                            <td>{{ $d->berat }} Kg</td>
                            <td>Rp {{ number_format($d->harga,0,',','.') }}</td>
                            <td class="fw-bold">
                                Rp {{ number_format($d->subtotal,0,',','.') }}
                            </td>
                        </tr>
                    @endforeach

                </tbody>

            </table>

        </div>
    </div>

    <div class="card">
    <div class="card-header bg-white fw-bold">
        Status Pembayaran
    </div>

    <div class="card-body">

        @if($data->status_pembayaran == 'Belum Bayar')

            <div class="alert alert-secondary">
                Mitra belum melakukan pembayaran.
            </div>

        @elseif($data->status_pembayaran == 'Menunggu Verifikasi')

            <div class="alert alert-warning">
                Mitra sudah mengirim pembayaran.
                Silakan lakukan verifikasi.
            </div>

            @if($data->bukti_transfer)

                <a href="{{ asset('storage/'.$data->bukti_transfer) }}"
                   target="_blank"
                   class="btn btn-info">

                    Lihat Bukti Transfer

                </a>

            @endif

            <form action="{{ route('pengiriman-mitra.verifikasi',$data->id) }}"
                  method="POST"
                  class="d-inline">

                @csrf

                <button class="btn btn-success">

                    ✔ Verifikasi Pembayaran

                </button>

            </form>

        @elseif($data->status_pembayaran == 'Lunas')

            <div class="alert alert-success">

                ✔ Pembayaran sudah diverifikasi.

            </div>

        @endif

    </div>
</div>
    

@endsection