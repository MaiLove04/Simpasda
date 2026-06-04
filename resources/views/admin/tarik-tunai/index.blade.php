@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">
                <i class="bi bi-cash-stack text-success me-2"></i>Mesin Kasir Tarik Tunai
            </h2>
            <p class="text-muted mb-0">Loket pencairan saldo tabungan sampah nasabah secara langsung (Konvensional/Manual).</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px; background-color: #dcfce7; color: #15803d;">
            <i class="bi bi-check-circle-fill me-2"></i><strong>{{ session('success') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px; background-color: #fee2e2; color: #991b1b;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>{{ session('error') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #16a34a, #15803d); border-radius: 16px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 text-uppercase fw-bold mb-1" style="font-size: 11px; letter-spacing: 1px;">Mode Operasional</h6>
                            <h3 class="fw-bold mb-0">Penarikan Kas</h3>
                        </div>
                        <i class="bi bi-wallet2 fs-1 text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-sm bg-white" style="border-radius: 16px; border-left: 5px solid #16a34a;">
                <div class="card-body p-4 d-flex align-items-center">
                    <i class="bi bi-shield-fill-check text-success fs-2 me-3"></i>
                    <div>
                        <strong class="text-dark d-block">Sistem Verifikasi Saldo Aman</strong>
                        <span class="text-muted small">Setiap pencairan otomatis memotong saldo utama nasabah secara *real-time* di database aplikasi.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
        <div class="card-body p-3 bg-light" style="border-radius: 16px;">
            <form action="{{ route('admin.tarik-tunai.index') }}" method="GET" class="row g-2">
                <div class="col-md-10">
                    <div class="input-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-person-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 py-2" value="{{ request('search') }}" placeholder="Ketik Nama Nasabah / Nomor Kode untuk memproses pencairan uang...">
                    </div>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-dark fw-bold shadow-sm" style="border-radius: 8px;">
                        <i class="bi bi-sliders me-1"></i> Saring Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 16px;">
        <div class="card-body p-0">
            <div class="table-responsive" style="border-radius: 16px 16px 0 0;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark text-white fw-bold" style="background-color: #0f172a;">
                        <tr>
                            <th class="ps-4 py-3" style="width: 80px;">ID</th>
                            <th class="py-3">No. Rekening Bank Sampah</th>
                            <th class="py-3">Nama Lengkap Nasabah</th>
                            <th class="py-3">Saldo Buku Tabungan</th>
                            <th class="pe-4 py-3 text-center" style="width: 200px;">Opsi Eksekusi</th>
                        </tr>
                    </thead>
                    <tbody class="text-dark">
                        @forelse($nasabahs as $index => $nasabah)
                        <tr>
                            <td class="ps-4 text-secondary fw-bold">{{ $nasabahs->firstItem() + $index }}</td>
                            <td>
                                <span class="badge bg-success-subtle text-success px-3 py-2 border border-success-subtle fw-bold" style="border-radius: 6px; font-size: 13px;">
                                    <i class="bi bi-hash me-1"></i>{{ $nasabah->kode_nasabah ?? '-' }}
                                </span>
                            </td>
                            <td class="fw-bold text-dark">{{ $nasabah->name }}</td>
                            <td>
                                <strong class="text-success" style="font-size: 16px;">
                                    Rp {{ number_format($nasabah->saldo, 0, ',', '.') }}
                                </strong>
                            </td>
                            <td class="pe-4 text-center">
                                <a href="{{ url('/admin/tarik-tunai/' . $nasabah->id) }}" class="btn btn-sm btn-success fw-bold px-3 py-2 border-0 shadow-sm" style="background-color: #16a34a; border-radius: 30px; font-size: 13px;">
                                    <i class="bi bi-cash me-1"></i> Cairkan Saldo
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-exclamation-octagon d-block mb-2 text-danger" style="font-size: 40px;"></i>
                                <span class="fw-bold d-block text-dark">Nasabah Tidak Ditemukan</span>
                                <span class="small text-muted">Pastikan nama benar atau saldo nasabah di atas Rp 0.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 py-3 px-4 d-flex justify-content-end" style="border-radius: 0 0 16px 16px;">
            {{ $nasabahs->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection