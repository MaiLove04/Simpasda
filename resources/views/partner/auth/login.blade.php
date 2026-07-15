<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Portal Partner | SIMPASDA</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
          rel="stylesheet">

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Segoe UI',sans-serif;
        }

        body{
            background:#f5f7fa;
        }

        .login-container{
            min-height:100vh;
            display:flex;
        }

        /* ================= LEFT ================= */

        .left-panel{

            width:45%;

            background:linear-gradient(
                180deg,
                #eefaf3,
                #dff4e7
            );

            display:flex;

            justify-content:center;

            align-items:center;

            border-right:1px solid #e5e7eb;

        }

        .left-content{

            text-align:center;

            width:85%;

            max-width:420px;

        }

        .left-content img{

            width:100%;

            max-width:320px;

            margin-bottom:25px;

        }

        .left-content h1{

            font-size:34px;

            color:#198754;

            font-weight:700;

            margin-bottom:15px;

        }

        .left-content p{

            color:#6b7280;

            line-height:1.7;

            font-size:15px;

        }

        /* ================= RIGHT ================= */

        .right-panel{

            width:55%;

            display:flex;

            justify-content:center;

            align-items:center;

            padding:40px;

        }

        .login-card{

            width:100%;

            max-width:420px;

            background:#fff;

            border-radius:18px;

            padding:45px;

            box-shadow:
                0 15px 40px rgba(0,0,0,.08);

        }

        .logo{

            width:70px;

            height:70px;

            background:#198754;

            color:white;

            display:flex;

            align-items:center;

            justify-content:center;

            border-radius:18px;

            margin:auto;

            font-size:30px;

            margin-bottom:20px;

        }

        .title{

            font-size:32px;

            font-weight:700;

            text-align:center;

            color:#1f2937;

        }

        .subtitle{

            text-align:center;

            color:#6b7280;

            margin-top:8px;

            margin-bottom:35px;

            font-size:15px;

        }

        .form-label{

            font-weight:600;

            margin-bottom:8px;

        }

        .form-control{

            height:52px;

            border-radius:12px;

            border:1px solid #d1d5db;

            padding-left:15px;

        }

        .form-control:focus{

            border-color:#198754;

            box-shadow:0 0 0 .18rem rgba(25,135,84,.15);

        }

        .btn-login{

            height:52px;

            border-radius:12px;

            font-weight:600;

            font-size:16px;

        }

        .copyright{

            text-align:center;

            margin-top:30px;

            color:#9ca3af;

            font-size:13px;

        }

        /* ================= MOBILE ================= */

        @media(max-width:992px){

            .left-panel{

                display:none;

            }

            .right-panel{

                width:100%;

                padding:25px;

            }

            .login-card{

                padding:35px;

            }

        }

    </style>

</head>

<body>

<div class="login-container">

    <!-- ================= LEFT ================= -->

    <div class="left-panel">

        <div class="left-content">

            <img src="{{ asset('images/coverlogin.png') }}">

            <h1>

                Portal Partner

            </h1>

            <p>

                Kelola penerimaan pengiriman sampah,
                konfirmasi pembayaran,
                dan pantau seluruh riwayat transaksi
                secara mudah melalui Portal Partner
                SIMPASDA.

            </p>

        </div>

    </div>

    <!-- ================= RIGHT ================= -->

    <div class="right-panel">

        <div class="login-card">

    <div class="logo">

        <i class="bi bi-recycle"></i>

    </div>

        <div class="title">

            Selamat Datang 👋

        </div>

        <div class="subtitle">

            Login menggunakan akun Partner Anda

        </div>

        @if(session('error'))

            <div class="alert alert-danger">

                {{ session('error') }}

            </div>

        @endif

        <form method="POST"
            action="{{ route('partner.login') }}">

            @csrf

            <div class="mb-3">

                <label class="form-label">

                    Email

                </label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="Masukkan email">

            </div>

            <div class="mb-4">

                <label class="form-label">

                    Password

                </label>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Masukkan password">

            </div>

            <button
                class="btn btn-success btn-login w-100">

                <i class="bi bi-box-arrow-in-right"></i>

                Masuk

            </button>

        </form>

        <div class="copyright">

            © {{ date('Y') }}

            <br>

            SIMPASDA - Portal Partner

        </div>

    </div>

    </div>

</div>

</body>

</html>