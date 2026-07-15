<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Operasional</title>

    <style>
        body{
            font-family: DejaVu Sans;
            font-size:12px;
            color:#000;
            margin:30px;
        }

        h2{
            text-align:center;
            margin-bottom:5px;
        }

        .periode{
            text-align:center;
            margin-bottom:20px;
            font-size:13px;
        }

        .tanggal{
            margin-bottom:15px;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        th, td{
            border:1px solid #000;
            padding:7px;
            font-size:11px;
        }

        th{
            background:#d9d9d9;
            text-align:center;
        }

        .center{
            text-align:center;
        }

        .right{
            text-align:right;
        }

        .summary{
            margin-top:20px;
            width:40%;
            margin-left:auto;
        }

        .summary td{
            border:1px solid #000;
        }

        .summary tr:last-child td{
            font-weight:bold;
            background:#eeeeee;
        }
    </style>

</head>

<body>

    <h2>LAPORAN DATA OPERASIONAL</h2>

    <div class="periode">
        Periode :
        {{ request('bulan')
            ? \Carbon\Carbon::parse(request('bulan'))->translatedFormat('F Y')
            : 'Seluruh Data' }}
    </div>

    <div class="tanggal">
        <strong>Tanggal Cetak :</strong>
        {{ now()->translatedFormat('d F Y H:i') }}
    </div>

    <table>

        <thead>

            <tr>
                <th width="40">No</th>
                <th width="90">Tanggal</th>
                <th width="90">Jenis</th>
                <th>Kategori</th>
                <th width="80">Harga</th>
                <th width="60">Jumlah</th>
                <th width="100">Total</th>
            </tr>

        </thead>

        <tbody>

        @foreach($operasional as $item)

            <tr>

                <td class="center">
                    {{ $loop->iteration }}
                </td>

                <td>
                    {{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}
                </td>

                <td>
                    {{ $item->jenis_transaksi }}
                </td>

                <td>
                    {{ $item->kategori }}
                </td>

                <td class="right">
                    Rp {{ number_format($item->harga,0,',','.') }}
                </td>

                <td class="center">
                    {{ $item->jumlah }}
                </td>

                <td class="right">
                    Rp {{ number_format($item->total,0,',','.') }}
                </td>

            </tr>

        @endforeach

        </tbody>

    </table>

    <table class="summary">

        <tr>
            <td>Total Pemasukan</td>
            <td class="right">
                Rp {{ number_format($totalPemasukan,0,',','.') }}
            </td>
        </tr>

        <tr>
            <td>Total Pengeluaran</td>
            <td class="right">
                Rp {{ number_format($totalPengeluaran,0,',','.') }}
            </td>
        </tr>

        <tr>
            <td>Saldo</td>
            <td class="right">
                Rp {{ number_format($saldo,0,',','.') }}
            </td>
        </tr>

    </table>

</body>

</html>