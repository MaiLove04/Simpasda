@extends('partner.layouts.app')

@section('title','Profil')

@section('content')

<div class="container-fluid">

    <div class="mb-4 text-center">
        <h2 class="fw-bold">
            <i class="bi bi-person-circle"></i>
            Profil Partner
        </h2>
        <p class="text-muted">
            Informasi akun dan data partner.
        </p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="row justify-content-center">

        <div class="col-lg-5 col-md-7">

            <div class="card shadow-sm border-0">

                <div class="card-body text-center py-5">

                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=198754&color=fff&size=150"
                         class="rounded-circle mb-4 shadow">

                    <h4 class="fw-bold">
                        {{ auth()->user()->name }}
                    </h4>

                    <p class="text-muted mb-3">
                        {{ auth()->user()->email }}
                    </p>

                    <span class="badge bg-success px-3 py-2">
                        Partner
                    </span>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection