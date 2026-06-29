@extends('layouts.admin')

@section('content')
<div class="container-fluid px-2 py-2">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1" style="color: #0f172a; font-weight: bold;">Master Jadwal Rutin</h1>
            <p class="text-muted mb-0" style="font-size: 14px;">Atur plotting penjemputan tetap mingguan atau interval untuk nasabah.</p>
        </div>
        <div class="d-flex gap-2">
            <!-- 🔥 TOMBOL GENERATE MANUAL -->
            <form action="{{ route('master-jadwal.generate') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mensinkronkan jadwal rutin ke tugas harian kurir untuk HARI INI?')">
                @csrf
                <button type="submit" class="btn btn-warning" style="background-color: #f59e0b; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600;">
                    🔄 Sinkronkan Jadwal Hari Ini
                </button>
            </form>

            <a href="{{ route('master-jadwal.create') }}" class="btn btn-primary" style="background-color: #16a34a; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600;">
                + Tambah Pola Rutin
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 mb-4 shadow-sm" role="alert" style="border-radius: 10px; background-color: #dcfce7; color: #15803d; font-weight: 500;">
            ✅ {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 p-4" style="border-radius: 16px; background: white;">
        <h3 class="h5 mb-4" style="color: #0f172a; font-weight: bold;">Daftar Master Penjemputan</h3>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" width="100%">
                <thead style="background-color: #f8fafc; color: #475569; font-weight: 600;">
                    <tr>
                        <th class="py-3 ps-3" width="5%">No</th>
                        <th class="py-3">Nama Nasabah</th>
                        <th class="py-3">Nama Kurir</th>
                        <th class="py-3">Pola Penjemputan</th>
                        <th class="py-3">Estimasi Jam</th>
                        <th class="py-3" width="10%">Status</th>
                        <th class="py-3 text-center" width="20%">Aksi</th>
                    </tr>
                </thead>
                <tbody style="color: #334155;">
                    @forelse($masterJadwals as $index => $master)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td class="py-3 ps-3">{{ $index + 1 }}</td>
                        <td class="py-3" style="font-weight: 600; color: #0f172a;">
                            {{ $master->nasabah->name ?? 'Nasabah' }}
                        </td>
                        <td class="py-3">
                            {{ $master->kurir->name ?? 'Belum Ditugaskan' }}
                        </td>
                        <td class="py-3">
                            @if($master->tipe_jadwal === 'interval')
                                <span class="badge px-2 py-2" style="font-size: 13px; border-radius: 6px; background-color: #ede9fe; color: #7c3aed; border: 1px solid #ddd6fe;">
                                    🔄 Setiap {{ $master->interval_hari }} hari
                                </span>
                                <br>
                                <small class="text-muted" style="font-size: 11px;">
                                    Mulai: {{ \Carbon\Carbon::parse($master->tanggal_mulai)->format('d M Y') }}
                                </small>
                            @else
                                <span class="badge bg-light text-dark border px-2 py-2" style="font-size: 13px; border-radius: 6px;">
                                    📅 {{ $master->hari_penjemputan }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3">
                            {{ \Carbon\Carbon::parse($master->jam_estimasi)->format('H:i') }} WIB
                        </td>
                        <td class="py-3">
                            @if($master->is_aktif)
                                <span class="badge" style="border-radius: 6px; padding: 6px 12px; font-weight: 600; font-size: 13px; background-color: #dcfce7; color: #16a34a;">Aktif</span>
                            @else
                                <span class="badge" style="border-radius: 6px; padding: 6px 12px; font-weight: 600; font-size: 13px; background-color: #fee2e2; color: #b91c1c;">Non-Aktif</span>
                            @endif
                        </td>
                        <td class="py-3 text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('master-jadwal.edit', $master->id) }}" class="btn btn-sm btn-outline-primary" style="border-radius: 6px; padding: 6px 12px; font-weight: 600;">
                                    ✏️
                                </a>

                                <form action="{{ route('master-jadwal.destroy', $master->id) }}" method="POST" onsubmit="return confirm('Hapus pola rutin penjemputan nasabah ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius: 6px; padding: 6px 12px; font-weight: 600;">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <div class="mb-2" style="font-size: 40px; color: #cbd5e1;">📋</div>
                            Belum ada master pola rutin yang didaftarkan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
