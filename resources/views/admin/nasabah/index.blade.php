@extends('layouts.admin')

@section('content')
<div class="container-fluid px-3 py-3">
    
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <div>
            <h1 class="h3 mb-1" style="color: #0f172a; font-weight: bold;">Data Nasabah</h1>
            <p class="text-muted mb-0" style="font-size: 13px;">Kelola akun nasabah, verifikasi status pendaftaran, dan cetak kartu QR Code.</p>
        </div>
        <button class="btn btn-sm btn-light border" style="height: 38px; font-weight: 600;" onclick="window.location.reload();">
            <i class="fas fa-sync-alt text-success"></i> Refresh
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" role="alert" style="border-radius: 8px; background-color: #dcfce7; color: #16a34a; font-size: 14px; font-weight: 600;">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0" style="border-radius: 12px; background: white;">
        
        <div class="d-flex justify-content-between align-items-center px-3 py-3 border-bottom">
            <h3 class="h6 mb-0" style="color: #0f172a; font-weight: bold;"><i class="fas fa-users me-2 text-muted"></i>Daftar Pemilik Akun</h3>
            
            <div style="width: 100%; max-width: 320px;">
                <form action="/admin/nasabah" method="GET" class="d-flex gap-2 mb-0">
                    <div class="input-group input-group-sm" style="border-radius: 6px; overflow: hidden; border: 1px solid #cbd5e1;">
                        <span class="input-group-text bg-white border-0 text-muted pe-1"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                        <input type="text" name="search" class="form-control border-0 ps-1" placeholder="Cari nama atau kode nasabah..." value="{{ request('search') }}" style="font-size: 12px; height: 32px;">
                    </div>
                    <button type="submit" class="btn btn-sm text-white px-3" style="border-radius: 6px; font-weight: 600; background-color: #1E521E; font-size: 12px; height: 32px;">Cari</button>
                    @if(request('search'))
                        <a href="/admin/nasabah" class="btn btn-sm btn-light border px-2 d-flex align-items-center justify-content-center" style="border-radius: 6px; font-size: 12px; height: 32px;">Reset</a>
                    @endif
                </form>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center" width="100%">
                <thead style="background-color: #f8fafc; color: #475569; font-weight: 600; font-size: 13px; border-bottom: 2px solid #e2e8f0;">
                    <tr>
                        <th class="py-2" width="4%">No</th>
                        <th class="py-2 text-start ps-3" width="18%">Nama Lengkap</th>
                        <th class="py-2 text-start" width="25%">Alamat Rumah</th>
                        <th class="py-2" width="13%">Kode Nasabah</th>
                        <th class="py-2" width="12%">QR Code Kartu</th>
                        <th class="py-2" width="15%">Status Verifikasi</th>
                        <th class="py-2 pe-3" width="13%">Aksi</th>
                    </tr>
                </thead>
                <tbody style="color: #334155; font-size: 13px;">
                    @forelse($nasabahs as $index => $nasabah)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td class="py-2 text-muted">{{ $nasabahs->firstItem() + $index }}</td>
                        <td class="py-2 text-start ps-3" style="font-weight: 600; color: #0f172a;">{{ $nasabah->name }}</td>
                        
                        <td class="py-2 text-start text-muted" style="font-size: 12px; max-width: 240px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $nasabah->alamat ?? '-' }}">
                            <i class="fas fa-map-marker-alt text-danger me-1" style="font-size: 11px;"></i>
                            {{ $nasabah->alamat ?? 'Alamat belum diisi' }}
                        </td>
                        
                        <td class="py-2">
                            @if($nasabah->kode_nasabah)
                                <span class="badge" style="background-color: #e8f5e9; color: #1e521e; font-weight: 700; font-size: 12px; padding: 5px 10px; border: 1px solid #c8e6c9; border-radius: 6px;">
                                    {{ $nasabah->kode_nasabah }}
                                </span>
                            @else
                                <span class="text-muted font-italic" style="font-size: 11px;">-</span>
                            @endif
                        </td>

                        <td class="py-2">
                            @if($nasabah->kode_nasabah)
                                <div class="p-2 d-inline-block bg-white border rounded shadow-sm" style="border-radius: 8px !important;">
                                    {!! QrCode::size(55)->generate($nasabah->kode_nasabah) !!}
                                </div>
                            @else
                                <span class="badge bg-light text-danger border" style="font-size: 11px; font-weight: 500;">Belum Generate</span>
                            @endif
                        </td>

                        <td class="py-2">
                            <form method="POST" action="/admin/nasabah/{{ $nasabah->id }}/status" class="m-0 d-inline-block">
                                @csrf
                                <select name="status" onchange="this.form.submit()" class="form-select form-select-sm fw-bold px-2 py-1" 
                                    style="font-size: 12px; border-radius: 6px; width: 125px; cursor: pointer;
                                    @if($nasabah->status == 'aktif') background-color: #dcfce7; color: #16a34a; border-color: #bbf7d0;
                                    @elseif($nasabah->status == 'pending') background-color: #fef3c7; color: #d97706; border-color: #fde68a;
                                    @else background-color: #fee2e2; color: #dc2626; border-color: #fecaca; @endif">
                                    <option value="pending" {{ $nasabah->status == 'pending' ? 'selected' : '' }}> Pending</option>
                                    <option value="aktif" {{ $nasabah->status == 'aktif' ? 'selected' : '' }}> Aktif</option>
                                    <option value="nonaktif" {{ $nasabah->status == 'nonaktif' ? 'selected' : '' }}> Nonaktif</option>
                                </select>
                            </form>
                        </td>

                        <td class="py-2 pe-3">
                            <div class="d-flex justify-content-center align-items-center gap-1">
                                @if($nasabah->kode_nasabah)
                                    <a href="{{ route('admin.nasabah.print-qr', $nasabah->id) }}" target="_blank" class="btn btn-sm text-white px-2 py-1" style="font-size: 11px; font-weight: 600; border-radius: 6px; height: 28px; background-color: #1E521E; border: none; display: inline-flex; align-items: center; gap: 4px;" title="Cetak QR Rumah Nasabah">
                                        <i class="fas fa-print"></i> Print
                                    </a>
                                @endif

                                <a href="/admin/nasabah/{{ $nasabah->id }}" class="btn btn-sm text-white px-2 py-1" style="font-size: 11px; font-weight: 600; border-radius: 6px; height: 28px; background-color: #0284c7; border: none; display: inline-flex; align-items: center; gap: 4px;" title="Detail Profile">
                                    <i class="fas fa-eye"></i> Detail
                                </a>

                                <form method="POST" action="/admin/nasabah/{{ $nasabah->id }}" class="m-0 d-inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm text-white px-2 py-1" style="font-size: 11px; font-weight: 600; border-radius: 6px; height: 28px; background-color: #dc2626; border: none; display: inline-flex; align-items: center; gap: 4px;" onclick="return confirm('Apakah Anda yakin ingin menghapus data nasabah {{ $nasabah->name }}?')" title="Hapus Akun">
                                        <i class="fas fa-trash-alt"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted" style="font-size: 13px;">
                            Tidak ditemukan data akun nasabah yang cocok.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
            <div class="text-muted" style="font-size: 12px;">
                Menampilkan {{ $nasabahs->firstItem() ?? 0 }}-{{ $nasabahs->lastItem() ?? 0 }} dari {{ $nasabahs->total() }} data nasabah
            </div>
            <div class="sm-pagination">
                {{ $nasabahs->appends(request()->input())->links('pagination::bootstrap-5') }}
            </div>
        </div>

    </div>
</div>

<style>
    .sm-pagination .pagination { margin-bottom: 0; gap: 2px; }
    .sm-pagination .page-link { padding: 4px 10px; font-size: 12px; border-radius: 4px; }
</style>
@endsection