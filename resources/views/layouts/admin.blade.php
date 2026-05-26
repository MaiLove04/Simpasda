<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASRI Admin Panel</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            background: #f8fafc;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: #0f172a;
            box-shadow: 4px 0 10px rgba(0,0,0,0.05);
            position: fixed;
            left: 0;
            top: 0;
        }

        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid #1e293b;
        }

        .sidebar-menu {
            padding: 15px 0;
        }

        .sidebar a {
            color: #94a3b8;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 12px 24px;
            font-weight: 500;
            font-size: 15px;
            transition: all 0.2s ease;
        }

        .sidebar a i {
            font-size: 18px;
            margin-right: 12px;
        }

        .sidebar a:hover {
            color: white;
            background: rgba(22, 163, 74, 0.1);
        }

        .sidebar a.active-menu {
            color: white;
            background: #16a34a;
            font-weight: 600;
        }

        /* Pembatas grup menu */
        .menu-divider {
            padding: 15px 24px 5px 24px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            font-weight: 700;
        }

        .main-wrapper {
            margin-left: 260px; /* Memberi ruang untuk sidebar fixed */
            width: calc(100% - 260px);
        }

        .content {
            padding: 40px;
        }

        /* Utilitas status */
        .status {
            padding: 6px 14px;
            border-radius: 30px;
            background: #dcfce7;
            color: #15803d;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
        }
    </style>
</head>
<body>

<div class="d-flex">

    <div class="sidebar">
        
        <div class="sidebar-header">
            <h4 class="text-white mb-0 fw-bold d-flex align-items-center">
                <i class="bi bi-recycle text-success me-2"></i> ASRI
            </h4>
            <small style="color: #64748b; font-size: 11px; font-weight: 600; letter-spacing: 0.5px;">BANK SAMPAH ADMIN</small>
        </div>

        <div class="sidebar-menu">
            <a href="/admin/dashboard" class="{{ Request::is('admin/dashboard*') ? 'active-menu' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <div class="menu-divider">Data Master</div>

            <a href="/admin/nasabah" class="{{ Request::is('admin/nasabah*') ? 'active-menu' : '' }}">
                <i class="bi bi-people"></i> Data Nasabah
            </a>

            <a href="/admin/kurir" class="{{ Request::is('admin/kurir*') ? 'active-menu' : '' }}">
                <i class="bi bi-truck"></i> Data Kurir
            </a>

            <a href="/admin/jenis-sampah" class="{{ Request::is('admin/jenis-sampah*') ? 'active-menu' : '' }}">
                <i class="bi bi-trash3"></i> Jenis Sampah
            </a>

            <div class="menu-divider">Logistik & Jadwal</div>

            <a href="{{ route('master-jadwal.index') }}" class="{{ Request::is('admin/master-jadwal*') ? 'active-menu' : '' }}">
                <i class="bi bi-arrow-repeat"></i> Pengaturan Jadwal Rutin
            </a>

            <a href="/admin/jadwal" class="{{ Request::is('admin/jadwal*') ? 'active-menu' : '' }}">
                <i class="bi bi-calendar-event"></i> Jadwal Harian Kurir
            </a>

            <div class="menu-divider">Transaksi</div>

            <a href="/admin/setor-sampah" class="{{ Request::is('admin/setor-sampah*') ? 'active-menu' : '' }}">
                <i class="bi bi-wallet2"></i> Setor Sampah
            </a>
        </div>

    </div>

    <div class="main-wrapper">
        <div class="content">
            @yield('content')
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>