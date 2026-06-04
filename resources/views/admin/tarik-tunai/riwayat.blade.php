@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    
    <!-- Header Halaman -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">
                <i class="bi bi-clock-history text-success me-2"></i>Jurnal Riwayat Penarikan
            </h2>
            <p class="text-muted mb-0">Arsip pembukuan seluruh transaksi penarikan saldo tunai manual yang berhasil dieksekusi.</p>
        </div>
        <div>
            <a href="{{ route('admin.tarik-tunai.index') }}" class="btn btn-success fw-bold px-3 py-2 border-0 shadow-sm" style="background-color: #16a34a; border-radius: 8px;">
                <i class="bi bi-cash-coin me-1"></i> Buka Loket Kasir
            </a>
        </div>
    </div>

    <!-- Kotak Penyaringan Multi-Filter (Nama & Tanggal) -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
        <div class="card-body p-3 bg-light" style="border-radius: 16px;">
            <form action="{{ route('admin.tarik-tunai.riwayat') }}" method="GET" class="row g-2">
                <!-- Filter Tanggal Transaksi -->
                <div class="col-md-3">
                    <div class="input-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-calendar3"></i></span>
                        <input type="date" name="date" class="form-control border-start-0 py-2" value="{{ request('date') }}">
                    </div>
                </div>
                <!-- Filter Nama / Kode Nasabah -->
                <div class="col-md-7">
                    <div class="input-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-person-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 py-2" value="{{ request('search') }}" placeholder="Cari berdasarkan nama atau kode rekening nasabah...">
                    </div>
                </div>
                <!-- Tombol Eksekusi -->
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-dark fw-bold shadow-sm" style="border-radius: 8px;">
                        <i class="bi bi-filter me-1"></i> Filter Jurnal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Log Pembukuan Mutasi -->
    <div class="card shadow-sm border-0" style="border-radius: 16px;">
        <div class="card-body p-0">
            <div class="table-responsive" style="border-radius: 16px 16px 0 0;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark text-white fw-bold" style="background-color: #0f172a;">
                        <tr>
                            <th class="ps-4 py-3" style="width: 80px;">No</th>
                            <th class="py-3">Waktu Transaksi</th>
                            <th class="py-3">No. Rekening</th>
                            <th class="py-3">Nama Nasabah</th>
                            <th class="py-3">Keterangan Jurnal</th>
                            <th class="pe-4 py-3 text-end" style="width: 220px;">Jumlah Penarikan</th>
                        </tr>
                    </thead>
                    <tbody class="text-dark">
                        @forelse($riwayat as $index => $log)
                        <tr>
                            <td class="ps-4 text-secondary fw-semibold">{{ $riwayat->firstItem() + $index }}</td>
                            <!-- Format Tanggal Jam Lokal -->
                            <td class="text-secondary small">
                                {{ $log->created_at->translatedFormat('d M Y') }}
                                <span class="d-block text-muted" style="font-size: 11px;">{{ $log->created_at->format('H:i') }} WIB</span>
                            </td>
                            <!-- Kode Rekening -->
                            <td>
                                <span class="badge bg-success-subtle text-success px-2 py-1 border border-success-subtle fw-bold" style="border-radius: 6px;">
                                    {{ $log->user->kode_nasabah ?? '-' }}
                                </span>
                            </td>
                            <!-- Nama Nasabah -->
                            <td class="fw-bold">{{ $log->user->name ?? 'User Terhapus' }}</td>
                            <!-- Keterangan Mutasi -->
                            <td class="text-muted small">{{ $log->keterangan }}</td>
                            <!-- Nominal Keluar Merah Bold -->
                            <td class="pe-4 text-end text-danger fw-bold" style="font-size: 15px;">
                                - Rp {{ number_format($log->nominal, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-folder-x d-block mb-2 text-secondary" style="font-size: 40px;"></i>
                                <span class="fw-bold d-block text-dark">Belum Ada Riwayat Penarikan</span>
                                <span class="small text-muted">Data transaksi keluar pada tanggal atau nama ini masih kosong.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Pagination Nav -->
        <div class="card-footer bg-white border-0 py-3 px-4 d-flex justify-content-end" style="border-radius: 0 0 16px 16px;">
            {{ $riwayat->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection