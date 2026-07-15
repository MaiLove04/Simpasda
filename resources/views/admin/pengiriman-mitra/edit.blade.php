@extends('layouts.admin')

@section('title', 'Edit Pengiriman')

@section('content')

<div class="container-fluid py-3">

    <div class="mb-4">
        <h3 class="fw-bold">
            ✏️ Edit Pengiriman
        </h3>
        <p class="text-muted">
            Edit data pengiriman (hanya sebelum diterima mitra)
        </p>
    </div>

    @if($pengiriman->status_pengiriman != 'Menunggu Mitra')
        <div class="alert alert-danger">
            Data tidak bisa diedit karena sudah diproses oleh mitra.
        </div>
    @else

    <form action="{{ route('pengiriman-mitra.update', $pengiriman->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- MITRA & TANGGAL --}}
        <div class="card mb-3">
            <div class="card-body row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">Mitra</label>
                    <select name="mitra_id" class="form-control">
                        @foreach($mitras as $m)
                            <option value="{{ $m->id }}"
                                {{ $pengiriman->mitra_id == $m->id ? 'selected' : '' }}>
                                {{ $m->nama_mitra }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal"
                        value="{{ $pengiriman->tanggal }}"
                        class="form-control">
                </div>

            </div>
        </div>

        {{-- DETAIL (SIMPLE VERSION) --}}
        <div class="card mb-3">
            <div class="card-body">

                <p class="text-muted">
                    ⚠️ Untuk edit detail, lebih aman dihapus & buat ulang (versi sederhana)
                </p>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Jenis</th>
                            <th>Berat</th>
                            <th>Harga</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($pengiriman->details as $i => $d)
                        <tr>
                            <td>
                                <input type="text"
                                    name="details[{{ $i }}][jenis_sampah]"
                                    value="{{ $d->jenis_sampah }}"
                                    class="form-control">
                            </td>

                            <td>
                                <input type="number"
                                    name="details[{{ $i }}][berat]"
                                    value="{{ $d->berat }}"
                                    class="form-control">
                            </td>

                            <td>
                                <input type="number"
                                    name="details[{{ $i }}][harga]"
                                    value="{{ $d->harga }}"
                                    class="form-control">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>

            </div>
        </div>

        <button class="btn btn-warning">
            Update
        </button>

    </form>

    @endif

</div>

@endsection