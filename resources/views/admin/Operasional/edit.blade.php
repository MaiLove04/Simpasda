@extends('layouts.admin')

@section('title', 'Edit Operasional')

@section('content')

<div class="container-fluid py-3">

    <div class="card shadow border-0">

        <div class="card-header bg-warning text-dark">

            <h4 class="mb-0">
                <i class="fas fa-edit me-2"></i>
                Edit Data Operasional
            </h4>

        </div>

        <div class="card-body">

            @if($errors->any())

                <div class="alert alert-danger">

                    <ul class="mb-0">

                        @foreach($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif

            <form action="{{ route('Operasional.update',$operasional->id) }}" method="POST">

                @csrf
                @method('PUT')

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">

                            Jenis Transaksi

                        </label>

                        <select
                            name="jenis_transaksi"
                            class="form-select"
                            required>

                            <option value="">-- Pilih --</option>

                            <option value="Pemasukan"
                                {{ old('jenis_transaksi',$operasional->jenis_transaksi)=='Pemasukan' ? 'selected' : '' }}>

                                Pemasukan

                            </option>

                            <option value="Pengeluaran"
                                {{ old('jenis_transaksi',$operasional->jenis_transaksi)=='Pengeluaran' ? 'selected' : '' }}>

                                Pengeluaran

                            </option>

                        </select>

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">

                            Tanggal

                        </label>

                        <input
                            type="date"
                            name="tanggal"
                            class="form-control"
                            value="{{ old('tanggal',$operasional->tanggal) }}"
                            required>

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">

                            Kategori

                        </label>

                        <input
                            type="text"
                            name="kategori"
                            class="form-control"
                            value="{{ old('kategori',$operasional->kategori) }}"
                            required>

                    </div>

                    <div class="col-md-3 mb-3">

                        <label class="form-label fw-semibold">

                            Harga

                        </label>

                        <input
                            type="number"
                            id="harga"
                            name="harga"
                            class="form-control"
                            value="{{ old('harga',$operasional->harga) }}"
                            min="0"
                            required>

                    </div>

                    <div class="col-md-3 mb-3">

                        <label class="form-label fw-semibold">

                            Jumlah

                        </label>

                        <input
                            type="number"
                            id="jumlah"
                            name="jumlah"
                            class="form-control"
                            value="{{ old('jumlah',$operasional->jumlah) }}"
                            min="1"
                            required>

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">

                            Total

                        </label>

                        <input
                            type="text"
                            id="total"
                            class="form-control bg-light"
                            readonly>

                    </div>

                    <div class="col-md-12 mb-3">

                        <label class="form-label fw-semibold">

                            Keterangan

                        </label>

                        <textarea
                            name="keterangan"
                            rows="3"
                            class="form-control">{{ old('keterangan',$operasional->keterangan) }}</textarea>

                    </div>

                </div>

                <div class="d-flex justify-content-end">

                    <a href="{{ route('Operasional.index') }}"
                       class="btn btn-secondary me-2">

                        <i class="fas fa-arrow-left"></i>

                        Kembali

                    </a>

                    <button
                        type="submit"
                        class="btn btn-warning">

                        <i class="fas fa-save"></i>

                        Update

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script>

function hitungTotal(){

    let harga = parseFloat(document.getElementById('harga').value) || 0;

    let jumlah = parseFloat(document.getElementById('jumlah').value) || 0;

    let total = harga * jumlah;

    document.getElementById('total').value =
        "Rp " + total.toLocaleString('id-ID');

}

document.getElementById('harga').addEventListener('keyup', hitungTotal);
document.getElementById('harga').addEventListener('change', hitungTotal);

document.getElementById('jumlah').addEventListener('keyup', hitungTotal);
document.getElementById('jumlah').addEventListener('change', hitungTotal);

hitungTotal();

</script>

@endsection