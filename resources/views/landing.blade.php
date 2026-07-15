<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SIMPASDA - Sistem Informasi Bank Sampah</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body{
            margin:0;
            font-family:'Segoe UI',sans-serif;
            background:#f5f7fa;
        }

        .hero{
            height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:20px;
            background:linear-gradient(135deg,#198754,#42b883);
            overflow:hidden;
        }

        .hero-card{
            max-width:1200px;
            width:100%;
            max-height:90vh;
            background:#fff;
            border-radius:25px;
            overflow:hidden;
            box-shadow:0 20px 60px rgba(0,0,0,.15);
        }

        .left-side{
            padding:40px;
        }

        .badge-title{
            margin-bottom:15px;
        }


        h1{
            font-size:36px;
            margin-bottom:8px;
        }

        .desc{
            font-size:16px;
            line-height:1.6;
            margin-bottom:20px;
        }


        .feature{
            margin-top:20px;
            margin-bottom:20px;
        }

        .feature li{
            margin-bottom:8px;
        }

        .btn-login{
            margin-top:15px;
        }
        .right-side{
            background:#eef8f2;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:40px;
        }

        .right-side img{
            width:100%;
            max-width:380px;
            max-height:70vh;
            object-fit:contain;
        }
        @media(max-width:991px){

            .left-side{
                padding:35px;
            }

            h1{
                font-size:32px;
            }

            .right-side{
                display:none;
            }

        }
    </style>

</head>

<body>

<div class="hero">

    <div class="hero-card">

        <div class="row g-0">

            <div class="col-lg-6">

                <div class="left-side">

                    <span class="badge-title">
                        WEBSITE RESMI SIMPASDA
                    </span>

                    <h1>
                        SIMPASDA
                    </h1>

                    <h4 class="mb-4">
                        Sistem Manajemen Sampah Daerah
                    </h4>

                    <p class="desc">
                        SIMPASDA merupakan sistem manajemen samoah daerah  berbasis
                        web yang memudahkan pengelolaan data nasabah, transaksi sampah, penjemputan,
                        dan laporan secara digital. 
                        Sistem ini mendukung operasional bank sampah agar lebih efektif, transparan, dan efisien.
                    </p>

                    <ul class="feature">

                        <li>♻️ Pengelolaan Data Nasabah</li>

                        <li>🚛 Penjadwalan Penjemputan Sampah</li>

                        <li>💰 Pengelolaan Saldo Nasabah</li>

                        <li>📊 Laporan Operasional Bank Sampah</li>

                        <li>🏭 Integrasi dengan Mitra Daur Ulang</li>

                    </ul>

                    <a href="{{ route('login') }}" class="btn btn-success btn-login">
                        Masuk ke Sistem
                    </a>

                </div>

            </div>

            <div class="col-lg-6">

                <div class="right-side">

                    <img src="{{ asset('images/latarcover.jpg') }}" alt="Bank Sampah">

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>