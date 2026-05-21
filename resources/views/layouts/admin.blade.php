<!DOCTYPE html>
<html>
<head>
    <title>ASRI Admin</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body{
            margin:0;
            background:#f5f7fa;
        }

        .sidebar{
            width:250px;
            min-height:100vh;
            background:#0f172a;
        }

        .sidebar a{
            color:white;
            text-decoration:none;
            display:block;
            padding:16px 20px;
        }

        .sidebar a:hover, .sidebar a.active-menu {
            background:#16a34a;
        }

        .content{
            flex:1;
            padding:30px;
        }

        .status{
            padding:6px 12px;
            border-radius:20px;
            background:#dcfce7;
            color:#15803d;
        }
    </style>
</head>
<body>

<div class="d-flex">

    <div class="sidebar">

        <h4 class="text-white p-3">
            ASRI
        </h4>

        <a href="/admin/dashboard" class="{{ Request::is('admin/dashboard*') ? 'active-menu' : '' }}">
            Dashboard
        </a>

        <a href="/admin/nasabah" class="{{ Request::is('admin/nasabah*') ? 'active-menu' : '' }}">
            Nasabah
        </a>

        <a href="/admin/kurir" class="{{ Request::is('admin/kurir*') ? 'active-menu' : '' }}">
            Kurir
        </a>

        <a href="/admin/jenis-sampah" class="{{ Request::is('admin/jenis-sampah*') ? 'active-menu' : '' }}">
            Jenis Sampah
        </a>

        <a href="/admin/jadwal" class="{{ Request::is('admin/jadwal*') ? 'active-menu' : '' }}">
            Jadwal
        </a>

        <a href="/admin/setor-sampah" class="{{ Request::is('admin/setor-sampah*') ? 'active-menu' : '' }}">
            Setor Sampah
        </a>

    </div>

    <div class="content">
        @yield('content')
    </div>

</div>

</body>
</html>