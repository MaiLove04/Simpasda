@extends('layouts.dlh')

@section('title', 'Kelola Bank Sampah - SIMPASDA')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">

    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">
                Kelola Bank Sampah
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Manajemen akun dan data bank sampah yang terdaftar pada sistem.
            </p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('dlh.bank-sampah.create') }}"
                class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">
                ➕ Tambah Bank Sampah
            </a>

            <a href=""
                class="px-4 py-2 bg-slate-600 text-white rounded-lg text-sm font-semibold hover:bg-slate-700">
                🔄 Refresh
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">

        <div class="p-5 border-b bg-slate-50">
            <h3 class="font-bold text-slate-700 uppercase text-sm">
                Data Bank Sampah
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 text-xs uppercase">
                        <th class="px-6 py-4 text-center">No</th>
                        <th class="px-6 py-4">Nama Bank Sampah</th>
                        <th class="px-6 py-4 text-center">Nasabah Aktif</th>
                        <th class="px-6 py-4">Alamat</th>
                        <th class="px-6 py-4 text-center">Total Sampah</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($bankSampahs as $index => $bank)
                    <tr class="hover:bg-slate-50">

                        <td class="px-6 py-4 text-center">
                            {{ $loop->iteration }}
                        </td>

                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-800">
                                {{ $bank->nama_bank }}
                            </div>
                        </td>

                        <td class="px-6 py-4 text-center">
                            {{ $bank->jumlah_nasabah }}
                        </td>

                        <td class="px-6 py-4">
                            {{ $bank->alamat }}
                        </td>

                        <td class="px-6 py-4 text-center">
                            {{ number_format($bank->total_sampah ?? 0,1,',','.') }} Kg
                        </td>

                        <td class="px-6 py-4 text-center">

                            @if($bank->status == 'active')
                                <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">
                                    Aktif
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-semibold">
                                    Tidak Aktif
                                </span>
                            @endif

                        </td>

                        <td class="px-6 py-4">
                            <div class="flex justify-center gap-2">

                                <a href="{{ route('dlh.bank-sampah.show',$bank->id) }}"
                                    class="px-3 py-1 bg-blue-100 text-blue-700 rounded text-xs">
                                    Detail
                                </a>

                                <a href="{{ route('dlh.bank-sampah.edit',$bank->id) }}"
                                    class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded text-xs">
                                    Edit
                                </a>

                                <form action="{{ route('dlh.bank-sampah.destroy',$bank->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Hapus data bank sampah ini?')">
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        class="px-3 py-1 bg-red-100 text-red-700 rounded text-xs">
                                        Hapus
                                    </button>
                                </form>

                            </div>
                        </td>

                    </tr>
                    @empty

                    <tr>
                        <td colspan="7" class="text-center py-12 text-slate-400">
                            Belum ada data bank sampah.
                        </td>
                    </tr>

                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t bg-slate-50">
            {{ $bankSampahs->links() }}
        </div>

    </div>

</div>
@endsection