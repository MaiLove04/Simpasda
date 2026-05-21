@extends('layouts.admin')

@section('content')


<div class="card p-4 shadow-sm">


    <div class="d-flex
                justify-content-between
                align-items-center">


        <div>

            <h2>

                Dashboard Admin

            </h2>


            <p>

                Halo,
                {{ auth()->user()->name }}

            </p>

        </div>



        <form
            method="POST"
            action="/admin/logout"
        >

            @csrf


            <button
                class="btn btn-danger"
                type="submit"
            >

                Logout

            </button>

        </form>


    </div>


    <hr>



    <div class="row mt-4">


        <div class="col-md-4 mb-3">

            <a
                href="/admin/kurir"
                class="btn btn-success w-100"
            >

                Kelola Kurir

            </a>

        </div>



        <div class="col-md-4 mb-3">

            <a
                href="/admin/jenis-sampah"
                class="btn btn-success w-100"
            >

                Jenis Sampah

            </a>

        </div>



        <div class="col-md-4 mb-3">

            <a
                href="/admin/jadwal"
                class="btn btn-success w-100"
            >

                Jadwal Kurir

            </a>

        </div>


    </div>


</div>


@endsection