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


        .sidebar a:hover{

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


        <a href="/admin/dashboard">

            Dashboard

        </a>

        <a href="/admin/nasabah">

            Nasabah

        </a>


        <a href="/admin/kurir">

            Kurir

        </a>



        <a href="/admin/jenis-sampah">

            Jenis Sampah

        </a>



        <a href="/admin/jadwal">

            Jadwal

        </a>


    </div>




    <div class="content">

        @yield('content')

    </div>


</div>



</body>
</html>