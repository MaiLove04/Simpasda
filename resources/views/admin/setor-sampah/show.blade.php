@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex flex-wrap align-items-center mb-4 gap-3">
        <a href="{{ route('admin.setor.index') }}" class="btn btn-light border"><i class="bi bi-arrow-left"></i> Kembali</a>
        <h2 class="mb-0 text-dark fw-bold">Detail Setoran Sampah</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- INFORMASI UMUM & UBAH STATUS -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="fw-bold mb-0">Informasi Transaksi</h5>
                </div>
                <div class="card-body p-4">
                    <p class="mb-1 text-muted small">Waktu Setor</p>
                    <p class="fw-bold">{{ \Carbon\Carbon::parse($setor->created_at)->format('d F Y, H:i') }} WIB</p>

                    <p class="mb-1 text-muted small">Nama Nasabah</p>
                    <p class="fw-bold">{{ $setor->nasabah->name ?? '-' }} ({{ $setor->nasabah->kode_nasabah ?? '-' }})</p>

                    <p class="mb-1 text-muted small">Penanggung Jawab Kurir</p>
                    <p class="fw-bold">{{ $setor->kurir->name ?? 'Kurir Default' }}</p>
                    
                    <hr>
                    
                    <p class="mb-2 fw-bold">Update Status Transaksi</p>
                    <form action="{{ route('admin.setor.updateStatus', $setor->id) }}" method="POST">
                        @csrf
                        <div class="input-group">
                            <select name="status" class="form-select">
                                <option value="menunggu_verifikasi" {{ $setor->status == 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                                <option value="selesai" {{ $setor->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                <option value="batal" {{ $setor->status == 'batal' ? 'selected' : '' }}>Batal</option>
                            </select>
                            <button class="btn btn-primary" type="submit">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- RINCIAN SAMPAH -->
        <div class="col-md-8 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="fw-bold mb-0">Manifes Rincian Sampah</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">No</th>
                                    <th>Jenis Sampah</th>
                                    <th class="text-center">Berat (Kg)</th>
                                    <th class="text-end">Harga/Kg</th>
                                    <th class="text-end pe-4">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($setor->details as $index => $detail)
                                <tr>
                                    <td class="ps-4">{{ $index + 1 }}</td>
                                    <td class="fw-bold">{{ $detail->jenisSampah->nama ?? 'Sampah' }}</td>
                                    <td class="text-center">{{ $detail->berat ?? '0' }} Kg</td>
                                    <td class="text-end text-muted">Rp {{ number_format($detail->harga_per_kg, 0, ',', '.') }}</td>
                                    <td class="text-end pe-4 text-success fw-bold">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada rincian sampah (Masih kosongan dari aplikasi Nasabah).</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end fs-5">Grand Total:</th>
                                    <th class="text-end pe-4 fs-5 text-success">Rp {{ number_format($setor->total, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
