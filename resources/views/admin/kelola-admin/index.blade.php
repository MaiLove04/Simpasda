@extends('layouts.admin')

@section('content')
<div class="container-fluid px-3 py-3">

    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <div>
            <h1 class="h3 mb-1" style="color:#0f172a;font-weight:bold;">
                Kelola Admin
            </h1>

            <p class="text-muted mb-0" style="font-size:13px;">
                Kelola akun administrator Bank Sampah.
            </p>
        </div>

        <a href="{{ route('kelola-admin.create') }}"
           class="btn btn-success btn-sm px-3"
           style="height:38px;font-weight:600;">
            <i class="fas fa-plus"></i>
            Tambah Admin
        </a>

    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}

        <button class="btn-close"
                data-bs-dismiss="alert">
        </button>
    </div>
    @endif

    <div class="card shadow-sm border-0"
         style="border-radius:12px;">

        <div class="d-flex justify-content-between align-items-center px-3 py-3 border-bottom">

            <h3 class="h6 mb-0">
                <i class="fas fa-user-shield me-2"></i>
                Daftar Administrator
            </h3>

            <div style="width:320px;">

                <form method="GET"
                      action="{{ route('kelola-admin.index') }}"
                      class="d-flex gap-2">

                    <div class="input-group input-group-sm">

                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>

                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            class="form-control"
                            placeholder="Cari nama atau email">

                    </div>

                    <button class="btn btn-success btn-sm">
                        Cari
                    </button>

                </form>

            </div>

        </div>

        <div class="table-responsive">

            <table class="table table-hover align-middle text-center">

                <thead>

                    <tr>

                        <th>No</th>

                        <th class="text-start">
                            Nama
                        </th>

                        <th class="text-start">
                            Email
                        </th>

                        <th>No HP</th>

                        <th>Status</th>

                        <th>Aksi</th>

                    </tr>

                </thead>

                <tbody>

                @forelse($admins as $index=>$admin)

                <tr>

                    <td>
                        {{ $admins->firstItem()+$index }}
                    </td>

                    <td class="text-start">
                        <strong>{{ $admin->name }}</strong>
                    </td>

                    <td class="text-start">
                        {{ $admin->email }}
                    </td>

                    <td>
                        {{ $admin->no_hp ?? '-' }}
                    </td>

                    <td>

                        @if(Auth::id() == $admin->id)

                            <span class="badge bg-success">
                                Akun Saya
                            </span>

                        @else

                            <form action="{{ route('kelola-admin.status',$admin->id) }}"
                                method="POST">

                                @csrf
                                @method('PATCH')

                                <select
                                    name="status"
                                    class="form-select form-select-sm"
                                    onchange="this.form.submit()">

                                    <option value="aktif"
                                        {{ $admin->status == 'aktif' ? 'selected' : '' }}>
                                        Aktif
                                    </option>

                                    <option value="nonaktif"
                                        {{ $admin->status == 'nonaktif' ? 'selected' : '' }}>
                                        Nonaktif
                                    </option>

                                </select>

                            </form>

                        @endif

                    </td>

                    <td>

                        <div class="d-flex justify-content-center gap-2">

                            <a href="{{ route('kelola-admin.edit', $admin->id) }}"
                            class="btn btn-info btn-sm text-white">

                                <i class="fas fa-eye"></i>
                                Detail

                            </a>

                        </div>

                    </td>

                </tr>

                @empty

                <tr>

                    <td colspan="6">

                        Tidak ada data admin.

                    </td>

                </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">

            <div style="font-size:12px;">

                Menampilkan

                {{ $admins->firstItem() ?? 0 }}

                -

                {{ $admins->lastItem() ?? 0 }}

                dari

                {{ $admins->total() }}

                data

            </div>

            {{ $admins->links('pagination::bootstrap-5') }}

        </div>

    </div>

</div>

@endsection