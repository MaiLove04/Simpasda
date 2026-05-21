@extends('layouts.admin')

@section('content')

<div class="card border-0 shadow-sm p-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold mb-1">
                Data Jenis Sampah
            </h3>

            <p class="text-muted mb-0">
                Kelola jenis sampah dan harga per kilogram
            </p>

        </div>

        <a
            href="/admin/jenis-sampah/create"
            class="btn btn-success"
        >
            + Tambah Jenis Sampah
        </a>

    </div>

    {{-- ALERT --}}
    @if(session('success'))

        <div class="alert alert-success">

            {{ session('success') }}

        </div>

    @endif

    {{-- TABLE --}}
    <div class="table-responsive">

        <table class="table table-hover align-middle text-center">

            <thead class="table-success">

                <tr>

                    <th width="70">
                        No
                    </th>

                    <th>
                        Nama Sampah
                    </th>

                    <th>
                        Harga / Kg
                    </th>

                    <th>
                        Status
                    </th>

                    <th width="220">
                        Aksi
                    </th>

                </tr>

            </thead>

            <tbody>

                @forelse($jenisSampahs as $index => $item)

                    <tr>

                        <td>

                            {{ $index + 1 }}

                        </td>

                        <td>

                            <span class="fw-semibold">

                                {{ $item->nama }}

                            </span>

                        </td>

                        <td>

                            <span class="fw-bold text-success">

                                Rp {{ number_format($item->harga_per_kg, 0, ',', '.') }}

                            </span>

                        </td>

                        <td>

                            @if($item->status == 'aktif')

                                <span class="badge bg-success">

                                    Aktif

                                </span>

                            @else

                                <span class="badge bg-danger">

                                    Nonaktif

                                </span>

                            @endif

                        </td>

                        <td>

                            <div class="d-flex justify-content-center gap-2">

                                <a
                                    href="/admin/jenis-sampah/{{ $item->id }}/edit"
                                    class="btn btn-warning btn-sm"
                                >
                                    Edit
                                </a>

                                <form
                                    action="/admin/jenis-sampah/{{ $item->id }}"
                                    method="POST"
                                >

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Yakin ingin menghapus data ini?')"
                                    >
                                        Hapus
                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="5">

                            <div class="py-4 text-muted">

                                Belum ada data jenis sampah

                            </div>

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection