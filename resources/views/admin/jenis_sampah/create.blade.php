@extends('layouts.admin')

@section('content')

<div class="card p-4 shadow-sm border-0">

    <h3 class="fw-bold mb-4">
        Tambah Jenis Sampah
    </h3>

    <form
        action="/admin/jenis-sampah"
        method="POST"
    >

        @csrf

        {{-- NAMA --}}
        <div class="mb-3">

            <label class="form-label">
                Nama Sampah
            </label>

            <input
                type="text"
                name="nama"
                class="form-control"
                required
            >

        </div>


        <!-- {{-- ICON --}}
        <div class="mb-3">

            <label class="form-label">
                Kode Icon
            </label>

            <input
                type="text"
                name="kode_icon"
                class="form-control"
                placeholder="plastik"
                required
            >

        </div> -->


        {{-- HARGA --}}
        <div class="mb-3">

            <label class="form-label">
                Harga Per Kg
            </label>

            <input
                type="number"
                name="harga_per_kg"
                class="form-control"
                required
            >

        </div>


        {{-- POIN --}}
        <div class="mb-3">

            <label class="form-label">
                Poin Per Kg
            </label>

            <input
                type="number"
                name="poin_per_kg"
                class="form-control"
                required
            >

        </div>


        {{-- STATUS --}}
        <div class="mb-4">

            <label class="form-label">
                Status
            </label>

            <select
                name="status"
                class="form-select"
            >

                <option value="aktif">
                    Aktif
                </option>

                <option value="nonaktif">
                    Nonaktif
                </option>

            </select>

        </div>


        <button
            type="submit"
            class="btn btn-success"
        >
            Simpan
        </button>

    </form>

</div>

@endsection