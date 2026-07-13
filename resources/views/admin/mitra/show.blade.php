@extends('layouts.admin')

@section('title','Detail Mitra')

@section('content')

<div class="container-fluid py-3">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold">
                <i class="bi bi-building text-success me-2"></i>
                Detail Mitra
            </h3>

            <p class="text-muted mb-0">
                Informasi lengkap mitra Bank Sampah.
            </p>

        </div>

        <a href="{{ route('Mitra.index') }}"
           class="btn btn-secondary">

            <i class="bi bi-arrow-left"></i>

            Kembali

        </a>

    </div>


    {{-- DATA MITRA --}}
    <div class="card shadow border-0 mb-4">

        <div class="card-header bg-white">

            <h5 class="fw-bold mb-0">

                Informasi Mitra

            </h5>

        </div>

        <div class="card-body">

            <table class="table table-borderless">

                <tr>
                    <th width="220">Nama Mitra</th>
                    <td>{{ $mitra->nama_mitra }}</td>
                </tr>

                <tr>
                    <th>Jenis Mitra</th>
                    <td>{{ $mitra->jenis_mitra }}</td>
                </tr>

                <tr>
                    <th>Penanggung Jawab</th>
                    <td>{{ $mitra->penanggung_jawab }}</td>
                </tr>

                <tr>
                    <th>No HP</th>
                    <td>{{ $mitra->no_hp }}</td>
                </tr>

                <tr>
                    <th>Email</th>
                    <td>{{ $mitra->email ?? '-' }}</td>
                </tr>

                <tr>
                    <th>Alamat</th>
                    <td>{{ $mitra->alamat }}</td>
                </tr>

                <tr>
                    <th>Status</th>

                    <td>

                        @if($mitra->status=="Aktif")

                            <span class="badge bg-success">
                                Aktif
                            </span>

                        @else

                            <span class="badge bg-danger">
                                Tidak Aktif
                            </span>

                        @endif

                    </td>

                </tr>

                <tr>
                    <th>Keterangan</th>
                    <td>{{ $mitra->keterangan ?? '-' }}</td>
                </tr>

            </table>

        </div>

    </div>


    {{-- AKUN LOGIN --}}
    <div class="card shadow border-0 mb-4">

        <div class="card-header bg-white">

            <h5 class="fw-bold mb-0">

                Akun Login Mitra

            </h5>

        </div>

        <div class="card-body">

            <table class="table table-borderless">

                <tr>

                    <th width="220">

                        Email Login

                    </th>

                    <td>

                        {{ $mitra->email ?? '-' }}

                    </td>

                </tr>

                <tr>

                    <th>

                        Status Akun

                    </th>

                    <td>

                        @if($mitra->user)

                            <span class="badge bg-success">

                                Sudah Memiliki Akun

                            </span>

                        @else

                            <span class="badge bg-danger">

                                Belum Memiliki Akun

                            </span>

                        @endif

                    </td>

                </tr>

            </table>

            <hr>

            @if(!$mitra->email)

                <div class="alert alert-warning mb-0">

                    Email belum diisi.
                    Isi email terlebih dahulu sebelum membuat akun login.

                </div>

            @elseif(!$mitra->user)

                <form action="{{ route('Mitra.buat-akun',$mitra->id) }}"
                      method="POST">

                    @csrf

                    <button class="btn btn-success">

                        <i class="bi bi-person-plus-fill"></i>

                        Buat Akun Login

                    </button>

                </form>

            @else

                <button
                    class="btn btn-warning"
                    data-bs-toggle="modal"
                    data-bs-target="#modalResetPassword">

                    <i class="bi bi-key-fill"></i>
                    Reset Password

                </button>

                

            @endif

        </div>

    </div>


    {{-- STATISTIK --}}
    <div class="row mb-4">

        <div class="col-md-3">

            <div class="card shadow border-0">

                <div class="card-body">

                    <small>Total Pengiriman</small>

                    <h3 class="fw-bold">

                        {{ $totalPengiriman }}

                    </h3>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card shadow border-0">

                <div class="card-body">

                    <small>Belum Lunas</small>

                    <h3 class="fw-bold text-danger">

                        {{ $belumLunas }}

                    </h3>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card shadow border-0">

                <div class="card-body">

                    <small>Sudah Lunas</small>

                    <h3 class="fw-bold text-success">

                        {{ $lunas }}

                    </h3>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card shadow border-0">

                <div class="card-body">

                    <small>Total Pendapatan</small>

                    <h5 class="fw-bold text-primary">

                        Rp {{ number_format($totalPendapatan,0,',','.') }}

                    </h5>

                </div>

            </div>

        </div>

    </div>


    {{-- RIWAYAT --}}
    <div class="card shadow border-0">

        <div class="card-header bg-white d-flex justify-content-between align-items-center">

            <h5 class="fw-bold mb-0">

                Riwayat Pengiriman

            </h5>

            <a href="{{ route('pengiriman-mitra.create') }}"
               class="btn btn-success btn-sm">

                <i class="bi bi-plus-circle"></i>

                Tambah Pengiriman

            </a>

        </div>

        <div class="card-body table-responsive">

            <table class="table table-hover align-middle">

                <thead class="table-success">

                    <tr>

                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Status Kirim</th>
                        <th>Status Bayar</th>
                        <th width="120">Aksi</th>

                    </tr>

                </thead>

                <tbody>

                @forelse($mitra->pengiriman as $item)

                    <tr>

                        <td>

                            {{ $item->kode_pengiriman }}

                        </td>

                        <td>

                            {{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}

                        </td>

                        <td>

                            Rp {{ number_format($item->total,0,',','.') }}

                        </td>

                        <td>

                            @if($item->status_pengiriman=='Menunggu Mitra')

                                <span class="badge bg-warning">

                                    Menunggu

                                </span>

                            @elseif($item->status_pengiriman=='Diterima')

                                <span class="badge bg-info">

                                    Diterima

                                </span>

                            @else

                                <span class="badge bg-success">

                                    Selesai

                                </span>

                            @endif

                        </td>

                        <td>

                            @if($item->status_pembayaran=='Lunas')

                                <span class="badge bg-success">

                                    Lunas

                                </span>

                            @elseif($item->status_pembayaran=='Menunggu Verifikasi')

                                <span class="badge bg-warning">

                                    Verifikasi

                                </span>

                            @else

                                <span class="badge bg-danger">

                                    Belum Bayar

                                </span>

                            @endif

                        </td>

                        <td>

                            <a href="{{ route('pengiriman-mitra.show',$item->id) }}"
                               class="btn btn-info btn-sm">

                                <i class="bi bi-eye"></i>

                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="6"
                            class="text-center text-muted">

                            Belum ada riwayat pengiriman.

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- Modal Reset Password -->
<div class="modal fade"
     id="modalResetPassword"
     tabindex="-1">

    <div class="modal-dialog">

        <div class="modal-content">

            <form action="{{ route('Mitra.resetPassword',$mitra->id) }}"
                  method="POST">

                @csrf

                <div class="modal-header">

                    <h5 class="modal-title">

                        Reset Password Mitra

                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label">
                            Password Baru
                        </label>
                        

                        <div class="input-group">

                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                required>

                            <button
                                class="btn btn-outline-secondary"
                                type="button"
                                id="togglePassword">

                                <i class="bi bi-eye"></i>

                            </button>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Konfirmasi Password
                            </label>

                            <div class="input-group">

                                <input
                                    type="password"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    class="form-control"
                                    required>

                                <button
                                    class="btn btn-outline-secondary"
                                    type="button"
                                    id="togglePassword2">

                                    <i class="bi bi-eye"></i>

                                </button>

                            </div>

                        </div>

                        <small class="text-muted">
                            Password minimal 8 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol.
                        </small>

                    <div id="passwordStrength"></div>

                </div>

                

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Batal

                    </button>

                    <button
                        class="btn btn-warning">

                        Simpan Password

                    </button>

                </div>

                @if($errors->any())

                <div class="alert alert-danger">

                    <ul class="mb-0">

                        @foreach($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

                @endif

            </form>

        </div>

    </div>

</div>

<script>

    const password = document.getElementById('password');
    const indicator = document.getElementById('passwordStrength');

    password.addEventListener('keyup', function () {

        let value = this.value;

        let score = 0;

        if(value.length >= 8) score++;
        if(/[A-Z]/.test(value)) score++;
        if(/[a-z]/.test(value)) score++;
        if(/[0-9]/.test(value)) score++;
        if(/[!@#$%^&*(),.?":{}|<>]/.test(value)) score++;

        if(score <= 2){

            indicator.innerHTML = "❌ Password Lemah";
            indicator.className = "text-danger fw-bold mt-2";

        }else if(score <=4){

            indicator.innerHTML = "⚠ Password Sedang";
            indicator.className = "text-warning fw-bold mt-2";

        }else{

            indicator.innerHTML = "✔ Password Kuat";
            indicator.className = "text-success fw-bold mt-2";

        }

    });

</script>

<script>

    function toggle(inputId,buttonId){

        const input=document.getElementById(inputId);

        const icon=document.querySelector("#"+buttonId+" i");

        if(input.type==="password"){

            input.type="text";

            icon.classList.remove("bi-eye");

            icon.classList.add("bi-eye-slash");

        }else{

            input.type="password";

            icon.classList.remove("bi-eye-slash");

            icon.classList.add("bi-eye");

        }

    }

    document.getElementById("togglePassword").onclick=function(){

        toggle("password","togglePassword");

    }

    document.getElementById("togglePassword2").onclick=function(){

        toggle("password_confirmation","togglePassword2");

    }

</script>   

@endsection