@extends('layouts.admin')

@section('content')

<div class="card p-4 shadow-sm border-0">

    <h3 class="fw-bold mb-4">
        Edit Jenis Sampah
    </h3>

    <form
        action="/admin/jenis-sampah/{{ $item->id }}"
        method="POST"
    >

        @csrf
        @method('PUT')

        {{-- NAMA --}}
        <div class="mb-3">

            <label class="form-label">
                Nama Sampah
            </label>

            <input
                type="text"
                name="nama"
                class="form-control"
                value="{{ $item->nama }}"
                required
            >

        </div>


        <!-- {{-- KODE ICON --}}
        <div class="mb-3">

            <label class="form-label">
                Kode Icon
            </label>

            <input
                type="text"
                name="kode_icon"
                class="form-control"
                value="{{ $item->kode_icon }}"
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
                value="{{ $item->harga_per_kg }}"
                required
            >

        </div>


        <!-- {{-- POIN --}}
        <div class="mb-3">

            <label class="form-label">
                Poin Per Kg
            </label>

            <input
                type="number"
                name="poin_per_kg"
                class="form-control"
                value="{{ $item->poin_per_kg }}"
                required
            >

        </div> -->


        {{-- STATUS --}}
        <div class="mb-4">

            <label class="form-label">
                Status
            </label>

            <select
                name="status"
                class="form-select"
            >

                <option
                    value="aktif"
                    {{ $item->status == 'aktif' ? 'selected' : '' }}
                >
                    Aktif
                </option>

                <option
                    value="nonaktif"
                    {{ $item->status == 'nonaktif' ? 'selected' : '' }}
                >
                    Nonaktif
                </option>

            </select>

        </div>


        <button
            type="submit"
            class="btn btn-success"
        >
            Update
        </button>

    </form>

</div>

@endsection