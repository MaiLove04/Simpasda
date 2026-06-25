@extends('layouts.admin')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">
                <i class="bi bi-cash-stack text-success me-2"></i>Antrean Request Tarik Tunai
            </h2>
            <p class="text-muted mb-0">Daftar permintaan pencairan saldo dari nasabah melalui aplikasi mobile.</p>
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

    <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
        <div class="card-body p-3 bg-light" style="border-radius: 16px;">
            <form action="{{ route('admin.tarik-tunai.index') }}" method="GET" class="row g-2">
                <div class="col-md-10">
                    <div class="input-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-person-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 py-2" value="{{ request('search') }}" placeholder="Cari Nama Nasabah atau Kode Nasabah...">
                    </div>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-dark fw-bold shadow-sm" style="border-radius: 8px;">
                        <i class="bi bi-sliders me-1"></i> Cari Request
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
                            <th class="ps-4 py-3">Nasabah</th>
                            <th class="py-3">Tanggal Request</th>
                            <th class="py-3">Nominal Tarik</th>
                            <th class="py-3">Saldo Saat Ini</th>
                            <th class="pe-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-dark">
                        @forelse($requests as $request)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar-sm bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
                                            {{ substr($request->user->name, 0, 1) }}
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">{{ $request->user->name }}</h6>
                                        <span class="text-muted small">ID: {{ $request->user->kode_nasabah }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted">{{ $request->tanggal_request->format('d M Y, H:i') }}</span>
                            </td>
                            <td>
                                <strong class="text-danger">
                                    Rp {{ number_format($request->jumlah_nominal, 0, ',', '.') }}
                                </strong>
                            </td>
                            <td>
                                <strong class="text-success">
                                    Rp {{ number_format($request->user->saldo, 0, ',', '.') }}
                                </strong>
                            </td>
                            <td class="pe-4 text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <form action="{{ route('admin.tarik-tunai.reject', $request->id) }}" method="POST" onsubmit="return confirm('Tolak request penarikan ini?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger fw-bold px-3 py-2" style="border-radius: 30px; font-size: 13px;">
                                            Tolak
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.tarik-tunai.approve', $request->id) }}" method="POST" onsubmit="return confirm('Serahkan uang tunai dan setujui penarikan ini?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success fw-bold px-3 py-2 border-0 shadow-sm" style="background-color: #16a34a; border-radius: 30px; font-size: 13px;">
                                            <i class="bi bi-check-lg me-1"></i> Approve & Bayar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox d-block mb-2 text-secondary" style="font-size: 40px;"></i>
                                <span class="fw-bold d-block text-dark">Belum Ada Request Pending</span>
                                <span class="small text-muted">Request dari nasabah melalui aplikasi akan muncul di sini.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 py-3 px-4 d-flex justify-content-end" style="border-radius: 0 0 16px 16px;">
            {{ $requests->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
