@extends('layouts.admin')

@section('content')


<div class="card p-4 shadow-sm">


    <h3>

        Detail Nasabah

    </h3>


    <hr>


    <p>

        Nama:
        {{ $nasabah->name }}

    </p>


    <p>

        Email:
        {{ $nasabah->email }}

    </p>


    <p>

        No HP:
        {{ $nasabah->no_hp }}

    </p>


    <p>

        Alamat:
        {{ $nasabah->alamat }}

    </p>


    <p>

        Status:
        {{ $nasabah->status }}

    </p>


</div>


@endsection