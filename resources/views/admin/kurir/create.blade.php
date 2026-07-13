@extends('layouts.admin')

@section('content')


<div class="card p-4 shadow-sm">


    <h3>

        Tambah Kurir

    </h3>



    <form
        method="POST"
        action="/admin/kurir"
        enctype="multipart/form-data"
    >

        @csrf



        <input
            class="form-control mb-3"
            name="name"
            placeholder="Nama"
        >




        <input
            class="form-control mb-3"
            name="email"
            placeholder="Email"
        >




        <input
            type="password"
            class="form-control mb-3"
            name="password"
            placeholder="Password"
        >

        <input
        type="password"
        class="form-control mb-3"
        name="password_confirmation"
        placeholder="Konfirmasi Password"
    >


        <input
            class="form-control mb-3"
            name="no_hp"
            placeholder="No HP"
        >




        <textarea
            class="form-control mb-3"
            name="alamat"
            placeholder="Alamat"
        ></textarea>




        <label class="mb-2">

            Foto Kurir

        </label>



        <input
            type="file"
            name="foto"
            class="form-control mb-3"
        >




        <button
            class="btn btn-success"
        >

            Simpan

        </button>


    </form>


</div>


@endsection