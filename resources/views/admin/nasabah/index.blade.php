@extends('layouts.admin')

@section('content')

<div class="card p-4 shadow-sm border-0">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h3 class="fw-bold mb-0">
            Data Nasabah
        </h3>

    </div>

    @if(session('success'))

        <div class="alert alert-success">

            {{ session('success') }}

        </div>

    @endif


    <div class="table-responsive">

        <table class="table table-bordered align-middle text-center">

            <thead class="table-success">

                <tr>

                    <th>No</th>

                    <th>Nama</th>

                    <th>Kode Nasabah</th>

                    <th>QR Code</th>

                    <th>Status</th>

                    <th>Aksi</th>

                </tr>

            </thead>

            <tbody>

                @foreach($nasabahs as $index => $nasabah)

                    <tr>

                        <td>
                            {{ $index + 1 }}
                        </td>

                        <td>
                            {{ $nasabah->name }}
                        </td>

                        {{-- KODE NASABAH --}}
                        <td>

                            <span class="badge bg-success fs-6">

                                {{ $nasabah->kode_nasabah }}

                            </span>

                        </td>

                        {{-- QR CODE --}}
                        <td>

                            @if($nasabah->kode_nasabah)

                                {!! QrCode::size(120)->generate($nasabah->kode_nasabah) !!}

                            @else

                                <span class="text-danger">
                                    Belum ada kode
                                </span>

                            @endif

                        </td>

                        {{-- STATUS --}}
                        <td>

                            <form
                                method="POST"
                                action="/admin/nasabah/{{ $nasabah->id }}/status"
                            >

                                @csrf

                                <select
                                    name="status"
                                    onchange="this.form.submit()"
                                    class="form-select form-select-sm"
                                >

                                    <option
                                        value="pending"
                                        {{ $nasabah->status == 'pending' ? 'selected' : '' }}
                                    >
                                        Pending
                                    </option>

                                    <option
                                        value="aktif"
                                        {{ $nasabah->status == 'aktif' ? 'selected' : '' }}
                                    >
                                        Aktif
                                    </option>

                                    <option
                                        value="nonaktif"
                                        {{ $nasabah->status == 'nonaktif' ? 'selected' : '' }}
                                    >
                                        Nonaktif
                                    </option>

                                </select>

                            </form>

                        </td>

                        {{-- AKSI --}}
                        <td>

                            <a
                                href="/admin/nasabah/{{ $nasabah->id }}"
                                class="btn btn-info btn-sm mb-1"
                            >
                                Detail
                            </a>

                            <form
                                method="POST"
                                action="/admin/nasabah/{{ $nasabah->id }}"
                                style="display:inline;"
                            >

                                @csrf
                                @method('DELETE')

                                <button
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Yakin hapus nasabah?')"
                                >
                                    Hapus
                                </button>

                            </form>

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    </div>

</div>

@endsection