<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halo, Admin SIMPASDA!</title>

    <!-- Bootstrap 5 & Google Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-bg: #0f172a;
            --sidebar-hover: rgba(255, 255, 255, 0.08);
            --active-green: #16a34a;
            --body-bg: #f8fafc;
        }

        body {
            margin: 0;
            background: var(--body-bg);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #334155;
            -webkit-font-smoothing: antialiased;
        }

        /* 📑 SIDEBAR PREMIUM */
        .sidebar {
            width: 270px;
            height: 100vh;
            max-height: 100vh; 
            background: var(--sidebar-bg);
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        .sidebar-header {
            padding: 28px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            flex-shrink: 0; 
        }

        .sidebar-menu {
            padding: 20px 0;
            overflow-y: auto; 
            flex-grow: 1;
        }

        /* Kustomisasi Scrollbar */
        .sidebar-menu::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar-menu::-webkit-scrollbar-track {
            background: var(--sidebar-bg);
        }
        .sidebar-menu::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        /* ⚡ Teks Utama di-set Putih Bersih (rgba 0.9) */
        .sidebar a, .sidebar .nav-link-btn {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 12px 24px;
            margin: 2px 14px;
            font-weight: 500;
            font-size: 14.5px;
            border-radius: 8px;
            background: transparent;
            border: none;
            width: calc(100% - 28px);
            text-align: left;
            transition: all 0.2s ease;
        }

        .sidebar a i, .sidebar .nav-link-btn i {
            font-size: 18px;
            margin-right: 14px;
        }

        /* Panah Dropdown otomatis */
        .sidebar .nav-link-btn .arrow-icon {
            margin-left: auto;
            margin-right: 0;
            font-size: 12px;
            transition: transform 0.2s ease;
        }
        .sidebar .nav-link-btn[aria-expanded="true"] .arrow-icon {
            transform: rotate(90deg);
        }

        .sidebar a:hover, .sidebar .nav-link-btn:hover {
            color: #ffffff; 
            background: var(--sidebar-hover);
        }

        /* Menu Utama Aktif */
        .sidebar a.active-menu, .sidebar .nav-link-btn.active-parent {
            color: #ffffff; 
            background: var(--active-green);
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25);
        }

        /* 📂 SUB MENU DROPDOWN DESIGN */
        .submenu-container {
            background: rgba(0, 0, 0, 0.2);
            margin: 0 14px;
            border-radius: 8px;
            padding: 4px 0;
        }

        .sidebar .submenu-container a {
            padding: 10px 16px 10px 48px;
            margin: 2px 0;
            font-size: 13.5px;
            color: rgba(255, 255, 255, 0.7);
            width: 100%;
        }

        .sidebar .submenu-container a:hover {
            color: #ffffff;
            background: transparent;
            text-decoration: underline;
        }

        .sidebar .submenu-container a.active-sub {
            color: #ffffff;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.08);
        }

        /* Divider Group */
        .menu-divider {
            padding: 20px 38px 8px 38px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #64748b;
            font-weight: 700;
        }

        /* MAIN CONTROLLER CONTAINER */
        .main-wrapper {
            margin-left: 270px;
            width: calc(100% - 270px);
            min-height: 100vh;
            overflow-x: auto;
            display: flex;
            flex-direction: column;
        }

        .content {
            width: 100%;
            padding: 35px;
            box-sizing: border-box;
        }

        .container-fluid{
            max-width:100%;
        }

        .card{
            overflow:hidden;
        }

        .table-responsive{
            overflow-x:auto;
        }

        h1,h2,h3,h4,h5{
            word-break:break-word;
        }

        
    </style>
</head>
<body>

<div class="d-flex">

    <!-- ================= SIDEBAR COMPONENT ================= -->
    <div class="sidebar">
        
        <div class="sidebar-header">
            <h4 class="text-white mb-1 fw-bold d-flex align-items-center" style="letter-spacing: -0.5px;">
                <i class="bi bi-recycle text-success me-2.5 fs-3"></i> SIMPASDA
            </h4>
            <div class="small fw-semibold text-uppercase tracking-wider" style="color: #64748b; font-size: 10px; letter-spacing: 1px;">BANK SAMPAH ADMIN</div>
        </div>

        <div class="sidebar-menu">
            <!-- Menu Utama: Dashboard -->
            <a href="/admin/dashboard" class="{{ Request::is('admin/dashboard*') ? 'active-menu' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>

                <!-- 1. DROPDOWN: DATA MASTER -->
                <button class="nav-link-btn {{ Request::is(
                    'admin/nasabah*',
                    'admin/kurir*',
                    'admin/jenis-sampah*',
                    'admin/kelola-admin*'
                ) ? 'active-parent' : '' }}"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#dropMaster"
                        aria-expanded="{{ Request::is(
                            'admin/nasabah*',
                            'admin/kurir*',
                            'admin/jenis-sampah*',
                            'admin/kelola-admin*'

                        ) ? 'true' : 'false' }}">

                    <i class="bi bi-folder-fill"></i>

                    Data Master

                    <i class="bi bi-chevron-right arrow-icon"></i>

                </button>

                <div class="collapse {{ Request::is(
                    'admin/nasabah*',
                    'admin/kurir*',
                    'admin/pengurus*',
                    'admin/mitra*',
                    'admin/pengiriman-mitra*',
                    'admin/jenis-sampah*',
                    'admin/kelola-admin*'
                ) ? 'show' : '' }}"
                id="dropMaster">

                    <div class="submenu-container">

                        <a href="{{ route('admin.nasabah.index') }}"
                        class="{{ Request::is('admin/nasabah*') ? 'active-sub' : '' }}">

                            Data Nasabah

                        </a>

                        <a href="{{ route('kurir-admin.index') }}"
                        class="{{ Request::is('admin/kurir*') ? 'active-sub' : '' }}">

                            Data Kurir

                        </a>
                        <a href="{{ route('kelola-admin.index') }}"
                        class="{{ Request::is('kelola/admin*') ? 'active-sub' : '' }}">

                            Kelola Admin

                        </a>
                        <a href="{{ route('jenis-sampah-admin.index') }}"
                        class="{{ Request::is('admin/jenis-sampah*') ? 'active-sub' : '' }}">

                            Jenis Sampah

                        </a>

                    </div>

                </div>

                <!-- 2. DROPDOWN: MITRA -->

            <button class="nav-link-btn {{ Request::is('admin/Mitra*','admin/pengiriman-mitra*') ? 'active-parent' : '' }}"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#dropMitra"
                    aria-expanded="{{ Request::is('admin/Mitra*','admin/pengiriman-mitra*') ? 'true':'false' }}">

                <i class="bi bi-people-fill"></i>

                Mitra

                <i class="bi bi-chevron-right arrow-icon"></i>

            </button>

            <div class="collapse {{ Request::is('admin/Mitra*','admin/pengiriman-mitra*') ? 'show':'' }}"
                id="dropMitra">

                <div class="submenu-container">

                    <a href="{{ route('Mitra.index') }}"
                    class="{{ Request::is('admin/Mitra*') ? 'active-sub':'' }}">

                        Data Mitra

                    </a>

                    <a href="{{ route('pengiriman-mitra.index') }}"
                    class="{{ Request::is('admin/pengiriman-mitra*') ? 'active-sub':'' }}">

                        Pengiriman Mitra

                    </a>

                </div>

            </div>

            <!-- 2. DROPDOWN: LOGISTIK -->
            <button class="nav-link-btn {{ Request::is('admin/master-jadwal*', 'admin/jadwal*') ? 'active-parent' : '' }}" 
                    type="button" data-bs-toggle="collapse" data-bs-target="#dropLogistik" aria-expanded="{{ Request::is('admin/master-jadwal*', 'admin/jadwal*') ? 'true' : 'false' }}">
                <i class="bi bi-calendar-range-fill"></i> Logistik & Jadwal
                <i class="bi bi-chevron-right arrow-icon"></i>
            </button>
            <div class="collapse {{ Request::is('admin/master-jadwal*', 'admin/jadwal*') ? 'show' : '' }}" id="dropLogistik">
                <div class="submenu-container">
                    <a href="{{ route('master-jadwal.index') }}" class="{{ Request::is('admin/master-jadwal*') ? 'active-sub' : '' }}">Pengaturan Jadwal</a>
                    <a href="/admin/jadwal" class="{{ Request::is('admin/jadwal*') ? 'active-sub' : '' }}">Jadwal Harian Kurir</a>
                </div>
            </div>

            <!-- 3. DROPDOWN: Transaksi -->
            <button class="nav-link-btn {{ Request::is('admin/setor-sampah*', 'admin/tarik-tunai*', 'admin/riwayat-penarikan*') ? 'active-parent' : '' }}" 
                    type="button" data-bs-toggle="collapse" data-bs-target="#dropKeuangan" aria-expanded="{{ Request::is('admin/setor-sampah*', 'admin/tarik-tunai*', 'admin/riwayat-penarikan*') ? 'true' : 'false' }}">
                <i class="bi bi-cash-stack"></i> Transaksi 
                <i class="bi bi-chevron-right arrow-icon"></i>
            </button>
            <div class="collapse {{ Request::is('admin/setor-sampah*', 'admin/tarik-tunai*', 'admin/riwayat-penarikan*') ? 'show' : '' }}" id="dropKeuangan">
                <div class="submenu-container">
                    <a href="/admin/setor-sampah" class="{{ Request::is('admin/setor-sampah*') ? 'active-sub' : '' }}">Setor Sampah</a>
                    <a href="{{ route('admin.tarik-tunai.index') }}" class="{{ Request::is('admin/tarik-tunai*') ? 'active-sub' : '' }}">Tarik Tunai Manual</a>
                    <a href="{{ route('admin.tarik-tunai.riwayat') }}" class="{{ Request::is('admin/riwayat-penarikan*') ? 'active-sub' : '' }}">Riwayat Penarikan</a>
                </div>
            </div>


            <!-- 4. DROPDOWN: OPERASIONAL -->
            <button class="nav-link-btn {{ Request::is('admin/Operasional*','admin/Gaji*') ? 'active-parent' : '' }}"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#dropOperasional"
                    aria-expanded="{{ Request::is('admin/Operasional*','admin/Gaji*') ? 'true' : 'false' }}"
                    aria-controls="dropOperasional">

                <i class="bi bi-building-gear"></i>

                Keuangan

                <i class="bi bi-chevron-right arrow-icon"></i>

            </button>

            <div class="collapse {{ Request::is('admin/Operasional*','admin/Gaji*') ? 'show' : '' }}"
                id="dropOperasional">

                <div class="submenu-container">

                    <a href="{{ route('Operasional.index') }}"
                    class="{{ Request::is('admin/Operasional*') ? 'active-sub' : '' }}">
                        Data Operasional
                    </a>

                </div>

            </div>

        </div> 
    </div> 

    <!-- ================= MAIN WRAPPER CONTENT ================= -->
    <div class="main-wrapper">
        <main class="content">
            @yield('content')
        </main>
    </div>

</div>

<!-- Bootstrap Bundle JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
