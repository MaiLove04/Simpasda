@extends('layouts.admin')

@section('content')
<div class="container-fluid px-2 py-2">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1" style="color: #0f172a; font-weight: bold;">Jadwal Kurir</h1>
            <p class="text-muted mb-0" style="font-size: 14px;">Pantau dan kelola plot penjemputan sampah dari nasabah ke kurir hari ini.</p>
        </div>
        <a href="{{ url('/admin/jadwal/create') }}" class="btn btn-primary" style="background-color: #16a34a; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600;">
            + Tambah Jadwal Manual
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 mb-4 shadow-sm" role="alert" style="border-radius: 10px; background-color: #dcfce7; color: #15803d; font-weight: 500;">
            ✅ {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 p-4" style="border-radius: 16px; background: white;">
        <h3 class="h5 mb-4" style="color: #0f172a; font-weight: bold;">Daftar Plot Penjemputan</h3>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" width="100%">
                <thead style="background-color: #f8fafc; color: #475569; font-weight: 600;">
                    <tr>
                        <th class="py-3 ps-3" width="5%">No</th>
                        <th class="py-3">Nama Nasabah</th>
                        <th class="py-3">Nama Kurir</th>
                        <th class="py-3">Alamat</th>
                        <th class="py-3">Tanggal Penjemputan</th>
                        <th class="py-3" width="12%">Status</th>
                        <th class="py-3 text-center" width="22%">Aksi</th>
                    </tr>
                </thead>
                <tbody style="color: #334155;">
                    @forelse($jadwals as $index => $jadwal)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td class="py-3 ps-3">{{ $index + 1 }}</td>
                        <td class="py-3" style="font-weight: 600; color: #0f172a;">
                            {{ $jadwal->nasabah->name ?? 'Nasabah ASRI' }}
                        </td>
                        <td class="py-3">
                            {{ $jadwal->kurir->name ?? 'Belum Ditugaskan' }}
                        </td>
                        <td class="py-3 text-truncate" style="max-width: 200px;" title="{{ $jadwal->alamat }}">
                            {{ $jadwal->alamat }}
                        </td>
                        <td class="py-3">
                            <span style="font-weight: 500; color: #334155;">
                                {{ \Carbon\Carbon::parse($jadwal->tanggal_penjemputan)->locale('id')->isoFormat('dddd, D MMM YYYY') }}
                            </span>
                            @if(!empty($jadwal->catatan))
                                <br>
                                <small class="text-muted" style="font-size: 11px; font-style: italic;">
                                    ℹ️ {{ Str::limit($jadwal->catatan, 30) }}
                                </small>
                            @endif
                        </td>
                        <td class="py-3">
                            @if(strtolower($jadwal->status) == 'terjadwal')
                                <span class="badge text-capitalize" style="border-radius: 6px; padding: 6px 12px; font-weight: 600; font-size: 13px; background-color: #e0f2fe; color: #0369a1;">{{ $jadwal->status }}</span>
                            @elseif(strtolower($jadwal->status) == 'proses')
                                <span class="badge text-capitalize" style="border-radius: 6px; padding: 6px 12px; font-weight: 600; font-size: 13px; background-color: #fef3c7; color: #b45309;">{{ $jadwal->status }}</span>
                            @elseif(strtolower($jadwal->status) == 'selesai')
                                <span class="badge text-capitalize" style="border-radius: 6px; padding: 6px 12px; font-weight: 600; font-size: 13px; background-color: #dcfce7; color: #16a34a;">{{ $jadwal->status }}</span>
                            @else
                                <span class="badge text-capitalize" style="border-radius: 6px; padding: 6px 12px; font-weight: 600; font-size: 13px; background-color: #fee2e2; color: #b91c1c;">{{ $jadwal->status }}</span>
                            @endif
                        </td>
                        
                        <td class="py-3 text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <button type="button" class="btn btn-sm btn-info text-white" style="border-radius: 6px; padding: 6px 10px; font-weight: 600;" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#detailModal{{ $jadwal->id }}">
                                    🔍 Detail
                                </button>

                                <a href="{{ route('jadwal.edit', $jadwal->id) }}" class="btn btn-sm btn-primary" style="border-radius: 6px; padding: 6px 10px; font-weight: 600; background-color: #3b82f6; border: none;">
                                    ✏️ Edit
                                </a>

                                <form action="{{ route('jadwal.destroy', $jadwal->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tiket jadwal harian ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" style="border-radius: 6px; padding: 6px 10px; font-weight: 600; background-color: #ef4444; border: none;">
                                        🗑️ Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <div class="mb-2" style="font-size: 40px; color: #cbd5e1;">📅</div>
                            Belum ada jadwal penjemputan harian saat ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach($jadwals as $jadwal)
        <div class="modal fade" id="detailModal{{ $jadwal->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 16px; border: none; text-align: left;">
                    <div class="modal-header bg-light" style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
                        <h5 class="modal-title" style="font-weight: 700; color: #0f172a;">📋 Detail Manifes Penjemputan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <table class="table table-borderless mb-0" style="font-size: 14px;">
                            <tr>
                                <td class="text-muted py-2" width="40%">Nama Nasabah</td>
                                <td class="py-2" style="font-weight: 600; color: #0f172a;">: {{ $jadwal->nasabah->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted py-2">Petugas Kurir</td>
                                <td class="py-2" style="font-weight: 600; color: #334155;">: 🚚 {{ $jadwal->kurir->name ?? 'Belum Alokasi' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted py-2">Hari & Tanggal</td>
                                <td class="py-2" style="font-weight: 600; color: #334155;">: {{ \Carbon\Carbon::parse($jadwal->tanggal_penjemputan)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted py-2">Status Manifes</td>
                                <td class="py-2">: 
                                    @if(strtolower($jadwal->status) == 'terjadwal')
                                        <span class="badge" style="border-radius: 6px; padding: 4px 8px; background-color: #e0f2fe; color: #0369a1;">{{ $jadwal->status }}</span>
                                    @elseif(strtolower($jadwal->status) == 'proses')
                                        <span class="badge" style="border-radius: 6px; padding: 4px 8px; background-color: #fef3c7; color: #b45309;">{{ $jadwal->status }}</span>
                                    @elseif(strtolower($jadwal->status) == 'selesai')
                                        <span class="badge" style="border-radius: 6px; padding: 4px 8px; background-color: #dcfce7; color: #16a34a;">{{ $jadwal->status }}</span>
                                    @else
                                        <span class="badge" style="border-radius: 6px; padding: 4px 8px; background-color: #fee2e2; color: #b91c1c;">{{ $jadwal->status }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted py-2">Alamat Tujuan</td>
                                <td class="py-2" style="color: #475569;">: {{ $jadwal->alamat }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted py-2">Catatan Lapangan</td>
                                <td class="py-2" style="color: #64748b; font-style: italic;">: {{ $jadwal->catatan ?? 'Tidak ada catatan khusus.' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="modal-footer bg-light" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                        <button type="button" class="btn btn-secondary" style="border-radius: 8px;" data-bs-dismiss="modal">Tutup</button>
                        <a href="{{ route('jadwal.edit', $jadwal->id) }}" class="btn btn-primary" style="background-color: #3b82f6; border: none; border-radius: 8px;">✏️ Ubah Jadwal</a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

</div>
@endsection