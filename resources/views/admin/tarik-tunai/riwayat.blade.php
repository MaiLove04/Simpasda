@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-clock-history text-primary me-2"></i>Riwayat Request Penarikan
            </h3>
            <p class="text-muted small mb-0">Laporan komprehensif seluruh transaksi pencairan saldo nasabah bank sampah.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-body p-3 bg-white" style="border-radius: 12px;">
            <form action="{{ route('admin.tarik-tunai.riwayat') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-12 col-md-9">
                    <div class="input-group" style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0 py-2.5 small" value="{{ request('search') }}" placeholder="Cari berdasarkan nama nasabah atau kode unik...">
                    </div>
                </div>
                <div class="col-12 col-md-3 d-grid">
                    <button type="submit" class="btn btn-primary fw-semibold py-2.5" style="border-radius: 8px; letter-spacing: 0.3px;">
                        <i class="bi bi-funnel me-1"></i> Saring Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light border-bottom text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 0.8px;">
                        <tr>
                            <th class="ps-4 py-3.5">Informasi Nasabah</th>
                            <th class="py-3.5">Nominal Penarikan</th>
                            <th class="py-3.5">Waktu Request</th>
                            <th class="py-3.5">Waktu Penyelesaian</th>
                            <th class="pe-4 py-3.5 text-center">Status Transaksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-secondary" style="font-size: 0.875rem;">
                        @forelse($riwayat as $item)
                        <tr class="border-bottom">
                            <td class="ps-4 py-3">
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold text-dark mb-0.5" style="font-size: 0.95rem;">{{ $item->user->name }}</span>
                                    <span class="text-muted text-uppercase" style="font-size: 0.75rem; font-family: monospace;">{{ $item->user->kode_nasabah }}</span>
                                </div>
                            </td>
                            <td class="py-3">
                                @if($item->status == 'approved')
                                    <span class="fw-bold text-success" style="font-size: 0.95rem;">Rp {{ number_format($item->jumlah_nominal, 0, ',', '.') }}</span>
                                @elseif($item->status == 'pending')
                                    <span class="fw-bold text-warning" style="font-size: 0.95rem;">Rp {{ number_format($item->jumlah_nominal, 0, ',', '.') }}</span>
                                @else
                                    <span class="fw-bold text-danger" style="font-size: 0.95rem;">Rp {{ number_format($item->jumlah_nominal, 0, ',', '.') }}</span>
                                @endif
                            </td>
                            <td class="py-3 text-dark">
                                <div>{{ $item->tanggal_request->format('d M Y') }}</div>
                                <div class="text-muted small" style="font-size: 0.75rem;">{{ $item->tanggal_request->format('H:i') }} WIB</div>
                            </td>
                            <td class="py-3 text-dark">
                                @if($item->tanggal_selesai)
                                    <div>{{ $item->tanggal_selesai->format('d M Y') }}</div>
                                    <div class="text-muted small" style="font-size: 0.75rem;">{{ $item->tanggal_selesai->format('H:i') }} WIB</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="pe-4 text-center py-3">
                                @if($item->status == 'approved')
                                    <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-2 text-uppercase" style="border-radius: 6px; font-size: 0.75rem; letter-spacing: 0.5px;">
                                        <i class="bi bi-check-circle-fill me-1"></i> Selesai
                                    </span>
                                @elseif($item->status == 'pending')
                                    <span class="badge bg-warning bg-opacity-10 text-warning fw-bold px-3 py-2 text-uppercase" style="border-radius: 6px; font-size: 0.75rem; letter-spacing: 0.5px;">
                                        <i class="bi bi-hourglass-split me-1"></i> Pending
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger fw-bold px-3 py-2 text-uppercase" style="border-radius: 6px; font-size: 0.75rem; letter-spacing: 0.5px;">
                                        <i class="bi bi-x-circle-fill me-1"></i> Ditolak
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <div class="mb-3">
                                    <i class="bi bi-folder-x text-muted opacity-50" style="font-size: 3rem;"></i>
                                </div>
                                <h6 class="fw-semibold text-dark mb-1">Data Riwayat Kosong</h6>
                                <p class="small text-muted mb-0">Tidak ditemukan rekaman transaksi penarikan saldo pada sistem.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center" style="border-radius: 0 0 12px 12px;">
            <span class="text-muted small">Menampilkan data halaman ini</span>
            <div>
                {{ $riwayat->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection