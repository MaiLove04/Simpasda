@extends('layouts.admin')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">
                <i class="bi bi-clock-history text-primary me-2"></i>Riwayat Request Penarikan
            </h2>
            <p class="text-muted mb-0">Laporan transaksi pencairan saldo yang telah diproses oleh admin.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
        <div class="card-body p-3 bg-light" style="border-radius: 16px;">
            <form action="{{ route('admin.tarik-tunai.riwayat') }}" method="GET" class="row g-2">
                <div class="col-md-10">
                    <div class="input-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 py-2" value="{{ request('search') }}" placeholder="Cari Nama Nasabah...">
                    </div>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary fw-bold shadow-sm" style="border-radius: 8px;">
                        Filter Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 16px;">
        <div class="card-body p-0">
            <div class="table-responsive" style="border-radius: 16px 16px 0 0;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-secondary fw-bold">
                        <tr>
                            <th class="ps-4 py-3">Nasabah</th>
                            <th class="py-3">Nominal</th>
                            <th class="py-3">Tgl Request</th>
                            <th class="py-3">Tgl Selesai</th>
                            <th class="pe-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-dark">
                        @forelse($riwayat as $item)
                        <tr>
                            <td class="ps-4">
                                <h6 class="mb-0 fw-bold">{{ $item->user->name }}</h6>
                                <span class="text-muted small">{{ $item->user->kode_nasabah }}</span>
                            </td>
                            <td>
                                <strong class="{{ $item->status == 'approved' ? 'text-success' : 'text-danger' }}">
                                    Rp {{ number_format($item->jumlah_nominal, 0, ',', '.') }}
                                </strong>
                            </td>
                            <td><small>{{ $item->tanggal_request->format('d/m/Y H:i') }}</small></td>
                            <td><small>{{ $item->tanggal_selesai ? $item->tanggal_selesai->format('d/m/Y H:i') : '-' }}</small></td>
                            <td class="pe-4 text-center">
                                @if($item->status == 'approved')
                                    <span class="badge bg-success-subtle text-success px-3 py-2" style="border-radius: 30px;">DISETUJUI</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger px-3 py-2" style="border-radius: 30px;">DITOLAK</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">Belum ada riwayat penarikan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 py-3 px-4 d-flex justify-content-end" style="border-radius: 0 0 16px 16px;">
            {{ $riwayat->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
