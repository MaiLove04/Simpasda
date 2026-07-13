@extends('layouts.dlh')

@section('title', 'Edit Bank Sampah - SIMPASDA')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-8">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">
            Edit Bank Sampah
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Perbarui informasi bank sampah.
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200">

        <form action="{{ route('dlh.bank-sampah.update', $bank->id) }}"
              method="POST">
            @csrf
            @method('PUT')

            <div class="p-6 space-y-5">

                {{-- Nama Bank Sampah --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Nama Bank Sampah
                    </label>

                    <input type="text"
                           name="nama_bank_sampah"
                           value="{{ old('nama_bank_sampah', $bank->nama_bank_sampah) }}"
                           class="w-full border border-slate-300 rounded-lg px-4 py-2"
                           required>

                    @error('nama_bank_sampah')
                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Jumlah Nasabah --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Jumlah Nasabah Aktif
                    </label>

                    <input type="number"
                           name="jumlah_nasabah"
                           value="{{ old('jumlah_nasabah', $bank->jumlah_nasabah) }}"
                           min="0"
                           class="w-full border border-slate-300 rounded-lg px-4 py-2">
                </div>

                {{-- Alamat --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Alamat
                    </label>

                    <textarea
                        name="alamat"
                        rows="4"
                        class="w-full border border-slate-300 rounded-lg px-4 py-2"
                        required>{{ old('alamat', $bank->alamat) }}</textarea>
                </div>

                {{-- Total Sampah --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Total Sampah (Kg)
                    </label>

                    <input type="number"
                           name="total_sampah"
                           value="{{ old('total_sampah', $bank->total_sampah) }}"
                           min="0"
                           step="0.01"
                           class="w-full border border-slate-300 rounded-lg px-4 py-2">
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Status
                    </label>

                    <select name="status"
                            class="w-full border border-slate-300 rounded-lg px-4 py-2">

                        <option value="aktif"
                            {{ $bank->status == 'aktif' ? 'selected' : '' }}>
                            Aktif
                        </option>

                        <option value="tidak_aktif"
                            {{ $bank->status == 'tidak_aktif' ? 'selected' : '' }}>
                            Tidak Aktif
                        </option>

                    </select>
                </div>

            </div>

            <div class="border-t px-6 py-4 flex justify-end gap-3 bg-slate-50">

                <a href="{{ route('dlh.bank-sampah.index') }}"
                   class="px-4 py-2 bg-slate-500 text-white rounded-lg hover:bg-slate-600">
                    Batal
                </a>

                <button type="submit"
                        class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
                    Update Data
                </button>

            </div>

        </form>

    </div>

</div>
@endsection