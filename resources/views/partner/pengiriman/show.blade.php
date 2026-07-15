@extends('partner.layouts.app')

@section('title','Detail Pengiriman')

@section('content')

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="mb-4">

        <h2 class="fw-bold">

            📦 Detail Pengiriman

        </h2>

        <p class="text-muted">

            {{ $data->kode_pengiriman }}

        </p>

    </div>

    {{--  CARD  --}}
    <div class="container-fluid">

        <div class="card-body">

            <div class="row">

                <div class="col-md-8">

                    <h4 class="fw-bold">

                        {{ $data->mitra->nama_mitra }}

                    </h4>

                    <p class="text-muted mb-1">

                        Pengiriman dari Bank Sampah

                    </p>

                </div>

                <div class="col-md-4 text-end">

                    <span class="badge bg-warning">

                        {{ $data->status_pengiriman }}

                    </span>

                </div>

            </div>

            <hr>

            <div class="row">

                <div class="col-md-4">

                    <small class="text-muted">

                        Total Berat

                    </small>

                    <h5 class="fw-bold">

                        {{ $data->details->sum('berat') }} Kg

                    </h5>

                </div>

                <div class="col-md-4">

                    <small class="text-muted">

                        Total Pembayaran

                    </small>

                    <h5 class="fw-bold text-success">

                        Rp {{ number_format($data->total,0,',','.') }}

                    </h5>

                </div>

                <div class="col-md-4">

                    <small class="text-muted">

                        Status Pembayaran

                    </small>

                    <h5>

                        {{ $data->status_pembayaran }}

                    </h5>

                </div>

            </div>

        </div>

    </div>
    {{--  INFORMASI PENGIRIMAN --}}
    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-white fw-bold">

            Informasi Pengiriman

        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-6 mb-3">

                    <small class="text-muted">

                        Tanggal Pengiriman

                    </small>

                    <div>

                        {{ \Carbon\Carbon::parse($data->tanggal)->format('d M Y') }}

                    </div>

                </div>

                <div class="col-md-6 mb-3">

                    <small class="text-muted">

                        Kode Pengiriman

                    </small>

                    <div>

                        {{ $data->kode_pengiriman }}

                    </div>

                </div>

                <div class="col-md-6">

                    <small class="text-muted">

                        Status Pengiriman

                    </small>

                    <div>

                        {{ $data->status_pengiriman }}

                    </div>

                </div>

                <div class="col-md-6">

                    <small class="text-muted">

                        Status Pembayaran

                    </small>

                    <div>

                        {{ $data->status_pembayaran }}

                    </div>

                </div>

            </div>

        </div>

    </div>

    
    {{--  TABEL BARANG --}}
    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-white fw-bold">

            Barang Yang Dikirim

        </div>

        <div class="card-body table-responsive">

            <table class="table align-middle">

                <thead class="table-success">

                    <tr>

                        <th>Jenis Sampah</th>

                        <th>Berat</th>

                        <th>Harga / Kg</th>

                        <th>Subtotal</th>

                    </tr>

                </thead>

                <tbody>

                @foreach($data->details as $item)

                    <tr>

                        <td>{{ $item->jenis_sampah }}</td>

                        <td>{{ $item->berat }} Kg</td>

                        <td>

                            Rp {{ number_format($item->harga,0,',','.') }}

                        </td>

                        <td>

                            Rp {{ number_format($item->subtotal,0,',','.') }}

                        </td>

                    </tr>

                @endforeach

                </tbody>

                <tfoot>

                    <tr>

                        <th colspan="3" class="text-end">

                            Total

                        </th>

                        <th>

                            Rp {{ number_format($data->total,0,',','.') }}

                        </th>

                    </tr>

                </tfoot>

            </table>

        </div>

    </div>

    

    {{--  PROGRES PENGIRIMAN --}}
    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-white fw-bold">

            Progress Pengiriman

        </div>

        <div class="card-body">

            <ul class="list-group list-group-flush">

                <li class="list-group-item">

                    ✅ Pengiriman Dibuat

                </li>

                <li class="list-group-item">

                    @if($data->status_pengiriman=="Menunggu Mitra")

                        🟡 Menunggu Konfirmasi Partner

                    @else

                        ✅ Barang Diterima

                    @endif

                </li>

                <li class="list-group-item">

                    @if($data->status_pembayaran=="Belum Bayar")

                        🔴 Menunggu Pembayaran

                        @elseif($data->status_pembayaran=="Menunggu Verifikasi")

                            🟡 Menunggu Verifikasi Admin

                        @else

                            ✅ Pembayaran Lunas

                    @endif

                </li>

            </ul>

        </div>

    </div>


    {{-- FORM PEMBAYARAN --}} 
<div class="card shadow-sm border-0 mt-4">

    <div class="card-header bg-white fw-bold">
        Action Partner
    </div>

    <div class="card-body">

        {{-- ================= KONFIRMASI BARANG ================= --}}
        @if($data->status_pengiriman == 'Menunggu Mitra')

            <div class="alert alert-warning">

                <h5 class="fw-bold mb-2">
                    📦 Barang Menunggu Konfirmasi
                </h5>

                <p class="mb-3">
                    Silakan cek barang yang telah diterima. Jika sudah sesuai,
                    klik tombol di bawah untuk mengonfirmasi penerimaan barang.
                </p>

                <form action="{{ route('partner.pengiriman.terima', $data->id) }}"
                      method="POST">

                    @csrf

                    <button class="btn btn-success">
                        <i class="bi bi-check-circle"></i>
                        Barang Sudah Diterima
                    </button>

                </form>

            </div>

        @endif



        {{-- ================= PEMBAYARAN ================= --}}
        @if(
                $data->status_pengiriman == 'Diterima'
                &&
                $data->status_pembayaran == 'Belum Bayar'
            )

            <div class="alert alert-primary">

                <h5 class="fw-bold">
                    💳 Pembayaran
                </h5>

                <p class="mb-1">
                    Total yang harus dibayar
                </p>

                <h2 class="fw-bold text-success">
                    Rp {{ number_format($data->total,0,',','.') }}
                </h2>

            </div>

            <form action="{{ route('partner.pengiriman.pembayaran', $data->id) }}"
                method="POST"
                enctype="multipart/form-data">

                @csrf

                <div class="mb-3">
                    <label class="form-label">Metode Pembayaran</label>

                    <select name="metode_pembayaran"
                            id="metode_pembayaran"
                            class="form-select"
                            required>

                        <option value="">-- Pilih Metode --</option>
                        <option value="Transfer">Transfer Bank</option>
                        <option value="Tunai">Tunai</option>

                    </select>
                </div>

                <div class="mb-3" id="uploadBukti" style="display:none;">
                    <label class="form-label">
                        Bukti Transfer
                    </label>

                    <input type="file"
                        name="bukti_transfer"
                        class="form-control"
                        accept=".jpg,.jpeg,.png,.pdf">

                    <small class="text-muted">
                        Format: JPG, PNG, PDF (Maks. 2 MB)
                    </small>
                </div>

                <button class="btn btn-primary">
                    Kirim Pembayaran
                </button>

            </form>

        @endif



        {{-- ================= MENUNGGU VERIFIKASI ================= --}}
        @if($data->status_pembayaran == 'Menunggu Verifikasi')

            <div class="alert alert-warning">

                <h5 class="fw-bold">
                    ⏳ Menunggu Verifikasi
                </h5>

                <p class="mb-2">
                    Pembayaran Anda sedang diperiksa oleh Admin.
                </p>

                <p class="mb-0">
                    Metode :
                    <strong>{{ $data->metode_pembayaran }}</strong>
                </p>

            </div>

            @if($data->bukti_transfer)

                <a href="{{ asset('storage/'.$data->bukti_transfer) }}"
                   target="_blank"
                   class="btn btn-outline-primary">

                    <i class="bi bi-image"></i>

                    Lihat Bukti Transfer

                </a>

            @endif

        @endif



        {{-- ================= LUNAS ================= --}}
        @if($data->status_pembayaran == 'Lunas')

            <div class="alert alert-success">

                <h5 class="fw-bold">
                    🎉 Pembayaran Berhasil
                </h5>

                <p class="mb-1">
                    Pembayaran telah diverifikasi oleh Admin.
                </p>

                <p class="mb-0">
                    Terima kasih telah bekerja sama.
                </p>

            </div>

        @endif

    </div>

</div>

<script>

const metode = document.getElementById('metode_pembayaran');
const upload = document.getElementById('uploadBukti');

metode.addEventListener('change', function(){

    if(this.value === 'Transfer'){
        upload.style.display = 'block';
    }else{
        upload.style.display = 'none';
    }

});

</script>

@endsection