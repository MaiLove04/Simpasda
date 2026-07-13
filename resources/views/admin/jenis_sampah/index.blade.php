@extends('layouts.admin')

@section('content')

<div class="card border-0 shadow-sm p-4">

    
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


    {{-- STATUS --}}
    <form method="GET" class="row mb-3">

        <div class="col-md-3">

            <select
                name="status"
                class="form-select"
                onchange="this.form.submit()"
            >

                <option value="semua"
                    {{ request('status') == 'semua' ? 'selected' : '' }}>
                    Semua Status
                </option>

                <option value="aktif"
                    {{ request('status') == 'aktif' ? 'selected' : '' }}>
                    Aktif
                </option>

                <option value="nonaktif"
                    {{ request('status') == 'nonaktif' ? 'selected' : '' }}>
                    Nonaktif
                </option>

            </select>

        </div>

    </form>
   
    <div class="table-responsive">

        <div class="row mb-4">

            <div class="col-md-4">

                <div class="card border-success">

                    <div class="card-body text-center">

                        <h5>{{ $total }}</h5>

                        <small>Total Jenis Sampah</small>

                    </div>

                </div>

            </div>

            <div class="col-md-4">

                <div class="card border-primary">

                    <div class="card-body text-center">

                        <h5>{{ $aktif }}</h5>

                        <small>Aktif</small>

                    </div>

                </div>

            </div>

            <div class="col-md-4">

                <div class="card border-secondary">

                    <div class="card-body text-center">

                        <h5>{{ $nonaktif }}</h5>

                        <small>Nonaktif</small>

                    </div>

                </div>

            </div>

        </div>

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

                            <span class="badge rounded-pill bg-success px-3">
                                🟢 Aktif
                            </span>

                            @else

                            <span class="badge rounded-pill bg-secondary px-3">
                                🔴 Nonaktif
                            </span>

                            @endif

                        </td>

                        <td>

                            <div class="d-flex justify-content-center gap-2">

                                @if($item->status == 'aktif')

                                    <a
                                        href="/admin/jenis-sampah/{{ $item->id }}/edit"
                                        class="btn btn-warning btn-sm"
                                    >
                                        ✏️ Edit
                                    </a>

                                @endif

                                <form
                                    action="{{ route('jenis-sampah.toggle-status', $item->id) }}"
                                    method="POST"
                                >

                                    @csrf
                                    @method('PUT')

                                    @if($item->status == 'aktif')

                                        <button
                                            class="btn btn-outline-danger btn-sm"
                                            onclick="return confirm('Nonaktifkan jenis sampah ini?')"
                                        >
                                            🚫 Nonaktifkan
                                        </button>

                                    @else

                                        <button
                                            class="btn btn-outline-success btn-sm"
                                            onclick="return confirm('Aktifkan kembali jenis sampah ini?')"
                                        >
                                            ✅ Aktifkan
                                        </button>

                                    @endif

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