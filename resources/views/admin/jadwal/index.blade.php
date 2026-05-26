@extends('layouts.admin')

@section('content')
<div class="container-fluid px-2 py-2">

    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1" style="color: #0f172a; font-weight: bold;">Jadwal Kurir</h1>
            <p class="text-muted mb-0" style="font-size: 14px;">Pantau dan kelola plot penjemputan sampah dari nasabah ke kurir hari ini.</p>
        </div>
        <a href="/admin/jadwal/create" class="btn btn-primary" style="background-color: #16a34a; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600;">
            + Tambah Jadwal Manual
        </a>
    </div>

    <!-- Alert Notification -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 mb-4 shadow-sm" role="alert" style="border-radius: 10px; background-color: #dcfce7; color: #15803d; font-weight: 500;">
            ✅ {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Data Table Card -->
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
                        <th class="py-3 text-center" width="15%">Aksi</th>
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
                            {{ \Carbon\Carbon::parse($jadwal->tanggal_penjemputan)->format('d M Y, H:i') }} WIB
                        </td>
                        <td class="py-3">
                            @if(strtolower($jadwal->status) == 'terjadwal')
                                <span class="badge text-capitalize" style="border-radius: 6px; padding: 6px 12px; font-weight: 600; font-size: 13px; background-color: #e0f2fe; color: #0369a1;">
                                    {{ $jadwal->status }}
                                </span>
                            @elseif(strtolower($jadwal->status) == 'proses')
                                <span class="badge text-capitalize" style="border-radius: 6px; padding: 6px 12px; font-weight: 600; font-size: 13px; background-color: #fef3c7; color: #b45309;">
                                    {{ $jadwal->status }}
                                </span>
                            @elseif(strtolower($jadwal->status) == 'selesai')
                                <span class="badge text-capitalize" style="border-radius: 6px; padding: 6px 12px; font-weight: 600; font-size: 13px; background-color: #dcfce7; color: #16a34a;">
                                    {{ $jadwal->status }}
                                </span>
                            @else
                                <span class="badge text-capitalize" style="border-radius: 6px; padding: 6px 12px; font-weight: 600; font-size: 13px; background-color: #fee2e2; color: #b91c1c;">
                                    {{ $jadwal->status }}
                                </span>
                            @endif
                        </td>
                        
                        <td class="py-3 text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <!-- Tombol Edit (Sudah Diperbaiki) -->
                                <a href="{{ route('jadwal.edit', $jadwal->id) }}" class="btn btn-sm btn-outline-warning" style="border-radius: 6px; padding: 6px 12px; font-weight: 600;">
                                    ✏️ Edit
                                </a>

                                <!-- Tombol Hapus -->
                                <form action="{{ route('jadwal.destroy', $jadwal->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tiket jadwal harian ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius: 6px; padding: 6px 12px; font-weight: 600;">
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
</div>
@endsection