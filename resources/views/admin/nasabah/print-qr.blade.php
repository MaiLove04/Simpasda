<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Card - Basayan Bestari</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* PANEL NAVIGASI ATAS */
        .no-print-area {
            background: white;
            padding: 15px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            margin-bottom: 30px;
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-save-img { background-color: #1E521E; color: white; }
        .btn-print { background-color: #0284c7; color: white; }
        .btn-back { background-color: #64748b; color: white; }

        /* KARTU KOTAK QR CODE MINIMALIS + NOTICE */
        .qr-card-container {
            background: white;
            width: 300px;
            padding: 24px;
            border: 4px solid #1E521E;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            box-sizing: border-box;
        }
        .brand-title {
            color: #1E521E;
            font-size: 18px;
            font-weight: 900;
            margin: 0 0 4px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .brand-subtitle {
            color: #64748b;
            font-size: 11px;
            font-weight: 600;
            margin: 0 0 16px 0;
        }
        .qr-box {
            background: #ffffff;
            padding: 12px;
            border-radius: 12px;
            display: inline-block;
            border: 1px solid #e2e8f0;
            margin-bottom: 14px;
        }
        .footer-notice {
            font-size: 10.5px;
            color: #64748b;
            font-weight: 600;
            border-top: 1px dashed #e2e8f0;
            padding-top: 10px;
            line-height: 1.4;
        }

        @media print {
            body { background-color: white; padding: 0; margin: 0; }
            .no-print-area { display: none !important; }
            .qr-card-container {
                box-shadow: none !important;
                position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            }
        }
    </style>
</head>
<body>

    <div class="no-print-area">
        <button class="btn btn-save-img" onclick="unduhKartuQr();">
            <i class="fas fa-file-image"></i> SIMPAN SEBAGAI FOTO
        </button>
        <button class="btn btn-print" onclick="window.print();">
            <i class="fas fa-print"></i> Cetak Printer
        </button>
        <a href="/admin/nasabah" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div id="area-kartu" class="qr-card-container">
        <div class="brand-title"><i class="fas fa-recycle"></i> Basayan Bestari</div>
        <div class="brand-subtitle">Bank Sampah Digital</div>
        
        <div class="qr-box">
            {!! QrCode::size(200)->generate($nasabah->kode_nasabah) !!}
        </div>

        <div class="footer-notice">
            <i class="fas fa-info-circle text-success"></i> Tempelkan kartu ini di area depan rumah yang mudah dijangkau / dipindai oleh Kurir Sampah.
        </div>
    </div>

    <script>
        function unduhKartuQr() {
            const elemenKartu = document.getElementById('area-kartu');
            
            html2canvas(elemenKartu, { scale: 2, useCORS: true }).then(canvas => {
                const fileGambar = canvas.toDataURL('image/png');
                const linkUnduh = document.createElement('a');
                linkUnduh.href = fileGambar;
                linkUnduh.download = 'QR_STICKER_{{ Str::slug($nasabah->name) }}.png';
                linkUnduh.click();
            });
        }
    </script>

</body>
</html>