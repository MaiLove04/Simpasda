<!DOCTYPE html>
<html lang="id">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title') | Portal Partner SIMPASDA</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>

        *{
            font-family:Inter,sans-serif;
        }

        body{

            background:#f5f7fb;

        }

        /* Sidebar */

        .sidebar{

            position:fixed;
            left:0;
            top:0;

            width:260px;
            height:100vh;

            background:#198754;

            color:white;

        }

        .logo{

            padding:28px;

            font-size:22px;

            font-weight:700;

            border-bottom:1px solid rgba(255,255,255,.15);

        }

        .sidebar a{

            display:flex;

            align-items:center;

            gap:12px;

            color:white;

            text-decoration:none;

            padding:14px 24px;

            transition:.2s;

        }

        .sidebar a:hover{

            background:rgba(255,255,255,.15);

        }

        .sidebar a.active{

            background:white;

            color:#198754;

            font-weight:600;

        }

        /* Content */

        .main{
            margin-left:260px;
            width:calc(100% - 260px);
            min-height:100vh;
        }

        /* Navbar */

        .topbar{

            background:white;

            padding:18px 35px;

            display:flex;

            justify-content:space-between;

            align-items:center;

            box-shadow:0 2px 10px rgba(0,0,0,.05);

        }

        .content{
            padding:35px;
            overflow-x:auto;
        }
        .partner-name{

            font-weight:600;

            color:#198754;

        }

        .logout{

            color:#dc3545;

            text-decoration:none;

        }

        .logout:hover{

            color:#b02a37;

        }

    </style>

</head>

<body>

<div class="sidebar">

    <div class="logo">

        ♻ Portal Partner

    </div>

    <a href="{{ route('partner.dashboard') }}"
       class="{{ Request::is('partner/dashboard') ? 'active' : '' }}">

        <i class="bi bi-speedometer2"></i>

        Dashboard

    </a>

    <a href="{{ route('pengiriman.index') }}"
    class="{{ Request::is('partner/pengiriman*') ? 'active' : '' }}">
        <i class="bi bi-box-seam"></i>
        Pengiriman Saya
    </a>

    <a href="{{ route('partner.pembayaran.index') }}"
    class="{{ Request::is('partner/pembayaran*') ? 'active' : '' }}">
        <i class="bi bi-receipt"></i>
        Riwayat Pembayaran
    </a>

    <a href="{{ route('profil.index') }}"
    class="{{ Request::is('partner/profil*') ? 'active' : '' }}">
        <i class="bi bi-person-circle"></i>
        Profil
    </a>

</div>

<div class="main">

    <div class="topbar">

        <div>

            Selamat Datang,

            <span class="partner-name">

                {{ auth()->user()->name }}

            </span>

        </div>

        <form action="{{ route('partner.logout') }}" method="POST">
            @csrf
            <button class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-right"></i>
                Logout
            </button>
        </form>

    </div>

    <div class="content">

        @yield('content')

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>