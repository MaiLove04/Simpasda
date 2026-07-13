@extends('layouts.admin')

@section('content')
<div class="container-fluid px-3 py-3">
    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-4 mb-3 pb-3 border-bottom">

    <!-- Judul -->
    <div>
        <h1 class="h3 mb-1 fw-bold" style="color:#0f172a;">
            Data Setor Sampah
        </h1>

        <p class="text-muted mb-0" style="font-size:13px;">
            Pantau log penyetoran multi-sampah secara real-time.
        </p>
    </div>

    <!-- Bagian Kanan -->
    <div class="d-flex align-items-start gap-3">

        <!-- Card Statistik -->
        <div class="d-flex gap-2">

            <div class="bg-white border rounded-3 px-3 py-2 text-center shadow-sm" style="min-width:130px;">
                <span class="text-muted d-block"
                      style="font-size:11px;font-weight:600;text-transform:uppercase;">
                    Total Transaksi
                </span>

                <span class="h5 mb-0 fw-bold" style="color:#0f172a;">
                    {{ $dataSetor->total() }}
                </span>
            </div>

            <div class="bg-white border rounded-3 px-3 py-2 text-center shadow-sm" style="min-width:180px;">
                <span class="text-muted d-block"
                      style="font-size:11px;font-weight:600;text-transform:uppercase;">
                    Perputaran Uang
                </span>

                <span class="h5 mb-0 fw-bold" style="color:#1E521E;">
                    Rp {{ number_format($dataSetor->sum('total'),0,',','.') }}
                </span>
            </div>

        </div>

        <!-- Tombol -->
        <div class="d-flex flex-column align-items-center">

            <a href="{{ route('admin.setor.manual') }}"
               class="btn btn-success px-4 py-3 fw-bold">
                <i class="bi bi-box-seam me-2"></i>
                Loket Setor Manual
            </a>

            <button class="btn btn-sm btn-light border mt-2"
                    style="height:38px;"
                    onclick="window.location.reload();">
                <i class="bi bi-arrow-clockwise text-success"></i>
            </button>

        </div>

    </div>

</div>

    <div class="card shadow-sm border-0" style="border-radius: 12px; background: white;">
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 px-3 py-3 border-bottom">
            <h3 class="h6 mb-0" style="color: #0f172a; font-weight: bold;"><i class="bi bi-clock-history me-2 text-muted"></i>Log Penyetoran Sampah</h3>
            
            <div class="w-100" style="max-width: 320px;">
                <form action="{{ route('admin.setor.index') }}" method="GET" class="d-flex gap-2 mb-0">
                    <div class="input-group input-group-sm" style="border-radius: 6px; overflow: hidden; border: 1px solid #cbd5e1;">
                        <span class="input-group-text bg-white border-0 text-muted pe-1"><i class="bi bi-search" style="font-size: 11px;"></i></span>
                        <input type="text" name="search" class="form-control border-0 ps-1" placeholder="Cari nasabah / kurir..." value="{{ request('search') }}" style="font-size: 12px; height: 32px;">
                    </div>
                    <button type="submit" class="btn btn-sm text-white px-3" style="border-radius: 6px; font-weight: 600; background-color: #1E521E; font-size: 12px; height: 32px;">Cari</button>
                    @if(request('search'))
                        <a href="{{ route('admin.setor.index') }}" class="btn btn-sm btn-light border px-2 d-flex align-items-center justify-content-center" style="border-radius: 6px; font-size: 12px; height: 32px;">Reset</a>
                    @endif
                </form>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" width="100%">
                <thead style="background-color: #f8fafc; color: #475569; font-weight: 600; font-size: 13px; border-bottom: 2px solid #e2e8f0;">
                    <tr>
                        <th class="py-2 ps-3" width="4%">No</th>
                        <th class="py-2" width="14%">Tanggal & Waktu</th>
                        <th class="py-2">Nama Nasabah</th>
                        <th class="py-2" style="color: #1E521E;">Nama Kurir</th>
                        <th class="py-2" width="25%">Rincian Sampah</th>
                        <th class="py-2">Total Uang</th>
                        <th class="py-2" width="10%">Foto</th>
                        <th class="py-2 pe-3" width="8%">Status</th>
                        <th class="py-2 pe-3 text-center" width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody style="color: #334155; font-size: 13px;">
                    @forelse($dataSetor as $index => $setor)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td class="py-2 ps-3 text-muted">{{ $dataSetor->firstItem() + $index }}</td>
                        <td class="py-2 text-muted" style="font-size: 12px;">{{ \Carbon\Carbon::parse($setor->created_at)->format('d M Y, H:i') }}</td>
                        <td class="py-2" style="font-weight: 600; color: #0f172a;">{{ $setor->nasabah->name ?? 'Nasabah ASRI' }}</td>
                        <td class="py-2" style="font-weight: 600; color: #1E521E;">{{ $setor->kurir->name ?? 'Kurir Lapangan' }}</td>
                        
                        <td class="py-2">
                            <div class="d-flex flex-column gap-1">
                                {{-- 1. Kondisi Data Baru Multi-Item --}}
                                @if($setor->details && $setor->details->count() > 0)
                                    @foreach($setor->details->take(2) as $detail)
                                        <div class="d-flex justify-content-between align-items-center" style="font-size: 12px; line-height: 1.4;">
                                            <div>
                                                <span style="font-weight: 600; color: #334155;">• {{ $detail->jenisSampah->nama ?? 'Jenis Sampah' }}</span>
                                                <span class="text-muted ms-1">({{ $detail->berat }} Kg)</span>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($setor->details->count() > 2)
                                        <small class="text-muted text-italic" style="font-size: 11px;">+{{ $setor->details->count() - 2 }} item lainnya...</small>
                                    @endif

                                {{-- 2. Fallback Data Lama Single-Item --}}
                                @elseif($setor->berat)
                                    <div class="d-flex justify-content-between align-items-center" style="font-size: 12px; line-height: 1.4; color: #b45309;">
                                        <div>
                                            <span style="font-weight: 600;">• {{ $setor->jenis_sampah->nama ?? 'Sampah' }}</span>
                                            <span class="ms-1">({{ $setor->berat }} Kg)</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted font-italic" style="font-size: 11px;">Tidak ada rincian</span>
                                @endif
                            </div>
                        </td>

                        <td class="py-2 font-weight-bold text-success" style="font-size: 14px; font-weight: 700;">Rp {{ number_format($setor->total, 0, ',', '.') }}</td>
                        
                        {{-- Kolom Foto yang Diperbaiki --}}
                        <td class="py-2">
                            @if($setor->foto)
                                <a href="{{ asset('storage/' . $setor->foto) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $setor->foto) }}" alt="Foto Bukti" class="img-thumbnail" style="max-height: 40px; max-width: 60px; object-fit: cover; border-radius: 4px;">
                                </a>
                            @else
                                <span class="text-muted" style="font-size: 11px;">Tidak ada foto</span>
                            @endif
                        </td>

                        <td class="py-2 pe-3">
                            <span class="badge" style="border-radius: 4px; padding: 4px 8px; font-weight: 600; font-size: 11px; background-color: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0;">
                                Selesai
                            </span>
                        </td>
                        <td class="py-2 pe-3">
                            <div class="d-flex gap-1 justify-content-center">
                                {{-- Tombol Detail memicu Modal Pop-up --}}
                                <button type="button" class="btn btn-sm btn-light border px-2" style="border-radius: 6px;" title="Lihat Detail" data-bs-toggle="modal" data-bs-target="#detailModal{{ $setor->id }}">
                                    <i class="bi bi-eye" style="font-size: 11px;"></i>
                                </button>
                                
                                <form action="{{ route('admin.setor.destroy', $setor->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus data setoran ini? Tindakan ini tidak dapat diurungkan.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light border px-2" style="border-radius: 6px;" title="Hapus Data">
                                        <i class="bi bi-trash text-danger" style="font-size: 11px;"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    {{-- MODAL POP-UP DETAIL TRANSAKSI --}}
                    <div class="modal fade" id="detailModal{{ $setor->id }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $setor->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content" style="border-radius: 12px; border: none; overflow: hidden;">
                                <div class="modal-header text-white px-3 py-3" style="background-color: #1E521E;">
                                    <h5 class="modal-title h6 mb-0 font-weight-bold" id="detailModalLabel{{ $setor->id }}"><i class="bi bi-receipt me-2"></i>Detail Transaksi Setor Sampah</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dash-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-3" style="font-size: 13px;">
                                    <div class="mb-3 pb-2 border-bottom">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <span class="text-muted d-block" style="font-size: 11px;">Tanggal & Waktu</span>
                                                <strong style="color: #0f172a;">{{ \Carbon\Carbon::parse($setor->created_at)->format('d F Y, H:i') }} WIB</strong>
                                            </div>
                                            <div class="col-6 text-end">
                                                <span class="text-muted d-block" style="font-size: 11px;">Status</span>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 10px; font-weight: 600;">Selesai</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="p-2 bg-light rounded-3 mb-2">
                                            <span class="text-muted d-block" style="font-size: 11px;">Nama Nasabah</span>
                                            <strong style="color: #0f172a; font-size: 13px;">{{ $setor->nasabah->name ?? 'Nasabah ASRI' }}</strong>
                                        </div>
                                        <div class="p-2 bg-light rounded-3">
                                            <span class="text-muted d-block" style="font-size: 11px;">Nama Kurir</span>
                                            <strong style="color: #1E521E; font-size: 13px;">{{ $setor->kurir->name ?? 'Kurir Lapangan' }}</strong>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <span class="text-muted d-block mb-1" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Rincian Item Sampah</span>
                                        <div class="border rounded-3 p-2 bg-white">
                                            @if($setor->details && $setor->details->count() > 0)
                                                @foreach($setor->details as $detail)
                                                    <div class="d-flex justify-content-between align-items-center py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                                                        <div>
                                                            <span style="font-weight: 600; color: #334155;">{{ $detail->jenisSampah->nama ?? 'Jenis Sampah' }}</span>
                                                            <span class="text-muted ms-1">({{ $detail->berat }} Kg)</span>
                                                        </div>
                                                        <span class="text-muted" style="font-family: monospace;">@Rp{{ number_format($detail->harga_per_kg, 0, ',', '.') }}</span>
                                                    </div>
                                                @endforeach
                                            @elseif($setor->berat)
                                                <div class="d-flex justify-content-between align-items-center py-1">
                                                    <div>
                                                        <span style="font-weight: 600; color: #b45309;">{{ $setor->jenis_sampah->nama ?? 'Sampah' }}</span>
                                                        <span class="ms-1">({{ $setor->berat }} Kg)</span>
                                                    </div>
                                                    <span style="font-family: monospace;">@Rp{{ number_format($setor->harga_per_kg, 0, ',', '.') }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted font-italic">Tidak ada rincian item.</span>
                                            @endif
                                        </div>
                                    </div>

                                    @if($setor->foto)
                                    <div class="mb-3">
                                        <span class="text-muted d-block mb-1" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Foto Bukti Setor</span>
                                        <div class="text-center bg-light border rounded-3 p-2">
                                            <img src="{{ asset('storage/' . $setor->foto) }}" alt="Foto Bukti" class="img-fluid rounded-2 shadow-sm" style="max-height: 180px; object-fit: contain;">
                                        </div>
                                    </div>
                                    @endif

                                    <div class="p-3 bg-success-subtle rounded-3 border border-success d-flex justify-content-between align-items-center">
                                        <span style="font-weight: 600; color: #1E521E;">Total Diterima Nasabah:</span>
                                        <span class="h5 mb-0 text-success" style="font-weight: 800;">Rp {{ number_format($setor->total, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light px-3 py-2 border-top">
                                    <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal" style="border-radius: 6px;">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted" style="font-size: 13px;">
                            Tidak ditemukan data setoran sampah yang cocok.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
               </tbody>
    </table>
</div>

{{-- Pagination --}}
<div class="d-flex justify-content-between align-items-center px-3 py-2 border-top bg-light"
     style="border-bottom-left-radius:12px; border-bottom-right-radius:12px;">

    <div class="text-muted" style="font-size:12px;">
        Menampilkan
        {{ $dataSetor->firstItem() ?? 0 }}
        -
        {{ $dataSetor->lastItem() ?? 0 }}
        dari
        {{ $dataSetor->total() }}
        data setor sampah
    </div>

    <div class="sm-pagination">
        {{ $dataSetor->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>

</div>

</div> {{-- card --}}
</div> {{-- container --}}

<style>
    .sm-pagination .pagination{
        margin-bottom: 0;
        gap: 2px;
    }

    .sm-pagination .page-link{
        padding: 4px 10px;
        font-size: 12px;
        border-radius: 4px;
    }
</style>

@endsection