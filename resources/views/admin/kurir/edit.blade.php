@extends('layouts.admin')

@section('content')


<div class="card p-4 shadow-sm">


    <h3>

        Edit Kurir

    </h3>




    <form
        method="POST"
        action="/admin/kurir/{{ $kurir->id }}"
    >

        @csrf

        @method('PUT')




        <input
            class="form-control mb-3"
            name="name"
            value="{{ $kurir->name }}"
        >




        <input
            class="form-control mb-3"
            name="email"
            value="{{ $kurir->email }}"
        >




        <input
            class="form-control mb-3"
            name="no_hp"
            value="{{ $kurir->no_hp }}"
        >




        <textarea
            class="form-control mb-3"
            name="alamat"
        >{{ $kurir->alamat }}</textarea>





        <button
            class="btn btn-success"
        >

            Update

        </button>


    </form>


</div>


@endsection