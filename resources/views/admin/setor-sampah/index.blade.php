@extends('layouts.admin')

@section('content')
<div class="container-fluid px-2 py-2">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1" style="color: #0f172a; font-weight: bold;">Data Setor Sampah</h1>
            <p class="text-muted mb-0" style="font-size: 14px;">Kelola, pantau, dan verifikasi data penyetoran sampah oleh kurir secara real-time.</p>
        </div>
        <button class="btn btn-primary" style="background-color: #16a34a; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600;" onclick="window.location.reload();">
            <i class="fas fa-sync-alt"></i> Refresh Data
        </button>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card p-3 border-0 shadow-sm" style="border-radius: 16px; background: white;">
                <span class="text-muted" style="font-size: 14px; font-weight: 500;">Total Setoran</span>
                <h2 class="mt-2 mb-0" style="font-weight: bold; color: #0f172a;">{{ $dataSetor->count() }}</h2>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card p-3 border-0 shadow-sm" style="border-radius: 16px; background: white;">
                <span class="text-muted" style="font-size: 14px; font-weight: 500;">Total Berat</span>
                <h2 class="mt-2 mb-0" style="font-weight: bold; color: #16a34a;">{{ $dataSetor->sum('berat') }} <span style="font-size: 16px; font-weight: normal;">Kg</span></h2>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card p-3 border-0 shadow-sm" style="border-radius: 16px; background: white;">
                <span class="text-muted" style="font-size: 14px; font-weight: 500;">Perputaran Uang</span>
                <h2 class="mt-2 mb-0" style="font-weight: bold; color: #2563eb;">Rp {{ number_format($dataSetor->sum('total'), 0, ',', '.') }}</h2>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 p-4" style="border-radius: 16px; background: white;">
        <h3 class="h5 mb-4" style="color: #0f172a; font-weight: bold;">Log Penyetoran Sampah</h3>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" width="100%">
                <thead style="background-color: #f8fafc; color: #475569; font-weight: 600;">
                    <tr>
                        <th class="py-3 ps-3" width="5%">No</th>
                        <th class="py-3">Tanggal & Waktu</th>
                        <th class="py-3">Nama Nasabah</th>
                        <th class="py-3">Berat</th>
                        <th class="py-3">Harga / Kg</th>
                        <th class="py-3">Total Uang</th>
                        <th class="py-3">Foto Bukti</th>
                        <th class="py-3 pe-3" width="12%">Status</th>
                    </tr>
                </thead>
                <tbody style="color: #334155;">
                    @forelse($dataSetor as $index => $setor)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td class="py-3 ps-3">{{ $index + 1 }}</td>
                        <td class="py-3">{{ \Carbon\Carbon::parse($setor->created_at)->format('d M Y, H:i') }} WIB</td>
                        <td class="py-3" style="font-weight: 600; color: #0f172a;">{{ $setor->user->name ?? 'Nasabah ASRI' }}</td>
                        <td class="py-3">{{ $setor->berat }} Kg</td>
                        <td class="py-3">Rp {{ number_format($setor->harga_per_kg, 0, ',', '.') }}</td>
                        <td class="py-3" style="font-weight: 600; color: #16a34a;">Rp {{ number_format($setor->total, 0, ',', '.') }}</td>
                        <td class="py-3">
                            @if($setor->foto_sampah)
                                <a href="{{ asset($setor->foto_sampah) }}" target="_blank">
                                    <img src="{{ asset($setor->foto_sampah) }}" alt="Foto Sampah" class="img-thumbnail" style="max-height: 45px; border-radius: 6px; object-fit: cover; border: 1px solid #e2e8f0;">
                                </a>
                            @else
                                <span class="text-muted font-italic" style="font-size: 13px;">Tanpa Foto</span>
                            @endif
                        </td>
                        <td class="py-3 pe-3">
                            <span class="badge text-capitalize" style="border-radius: 6px; padding: 6px 12px; font-weight: 600; font-size: 13px; background-color: #dcfce7; color: #16a34a;">
                                {{ $setor->status ?? 'Selesai' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <div class="mb-2" style="font-size: 40px; color: #cbd5e1;">📥</div>
                            Belum ada data setoran sampah yang masuk dari aplikasi kurir.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection