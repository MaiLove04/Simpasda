@extends('layouts.admin')

@section('title', 'Buat Pengiriman Mitra')

@section('content')

<div class="container-fluid py-3">

    <div class="mb-4">
        <h3 class="fw-bold">
            <i class="bi bi-truck"></i>
            Buat Pengiriman Mitra
        </h3>
        <p class="text-muted">
            Tambahkan pengiriman sampah ke mitra.
        </p>
    </div>

    <form action="{{ route('pengiriman-mitra.store') }}" method="POST">
        @csrf

        {{-- DATA MITRA --}}
        <div class="card shadow border-0 mb-3">
            <div class="card-body">

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Pilih Mitra
                        </label>

                        <select
                            name="mitra_id"
                            class="form-select"
                            required>

                            <option value="">
                                -- Pilih Mitra --
                            </option>

                            @foreach($mitras as $m)
                                <option value="{{ $m->id }}">
                                    {{ $m->nama_mitra }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Tanggal
                        </label>

                        <input
                            type="date"
                            name="tanggal"
                            class="form-control"
                            value="{{ date('Y-m-d') }}"
                            required>

                    </div>

                </div>

            </div>
        </div>

        {{-- DETAIL BARANG --}}
        <div class="card shadow border-0">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <h5 class="mb-0">
                        Detail Sampah
                    </h5>

                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        id="add-row">

                        <i class="bi bi-plus-circle"></i>

                        Tambah Barang

                    </button>

                </div>

                <div class="table-responsive">

                    <table
                        class="table table-bordered"
                        id="table-detail">

                        <thead class="table-success">

                            <tr>

                                <th>Jenis Sampah</th>
                                <th width="180">Berat (Kg)</th>
                                <th width="180">Harga / Kg</th>
                                <th width="70">Aksi</th>

                            </tr>

                        </thead>

                        <tbody>

                            <tr>

                                <td>

                                    <input
                                        type="text"
                                        name="details[0][jenis_sampah]"
                                        class="form-control"
                                        required>

                                </td>

                                <td>

                                    <input
                                        type="number"
                                        name="details[0][berat]"
                                        class="form-control"
                                        min="1"
                                        step="0.01"
                                        required>

                                </td>

                                <td>

                                    <input
                                        type="number"
                                        name="details[0][harga]"
                                        class="form-control"
                                        min="0"
                                        required>

                                </td>

                                <td class="text-center">

                                    <button
                                        type="button"
                                        class="btn btn-danger btn-sm remove-row">

                                        <i class="bi bi-trash"></i>

                                    </button>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

        <div class="mt-3">

            <button
                type="submit"
                class="btn btn-success">

                <i class="bi bi-check-circle"></i>

                Simpan Pengiriman

            </button>

            <a
                href="{{ route('pengiriman-mitra.index') }}"
                class="btn btn-secondary">

                Kembali

            </a>

        </div>

    </form>

</div>

<script>

document.addEventListener("DOMContentLoaded", function () {

    let index = 1;

    const tableBody = document.querySelector("#table-detail tbody");
    const btnTambah = document.getElementById("add-row");

    btnTambah.addEventListener("click", function () {

        const row = `
            <tr>

                <td>
                    <input
                        type="text"
                        name="details[${index}][jenis_sampah]"
                        class="form-control"
                        required>
                </td>

                <td>
                    <input
                        type="number"
                        name="details[${index}][berat]"
                        class="form-control"
                        min="1"
                        step="0.01"
                        required>
                </td>

                <td>
                    <input
                        type="number"
                        name="details[${index}][harga]"
                        class="form-control"
                        min="0"
                        required>
                </td>

                <td class="text-center">

                    <button
                        type="button"
                        class="btn btn-danger btn-sm remove-row">

                        <i class="bi bi-trash"></i>

                    </button>

                </td>

            </tr>
        `;

        tableBody.insertAdjacentHTML("beforeend", row);

        index++;

    });

    tableBody.addEventListener("click", function (e) {

        const btn = e.target.closest(".remove-row");

        if (!btn) return;

        if (tableBody.querySelectorAll("tr").length == 1) {

            alert("Minimal harus ada satu barang.");

            return;

        }

        btn.closest("tr").remove();

    });

});

</script>

@endsection