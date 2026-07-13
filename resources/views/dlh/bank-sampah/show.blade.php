@extends('layouts.dlh')

@section('title', 'Detail Validasi Unit - Simpasda')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-8">
    
    {{-- Kembali ke Dashboard --}}
    <div class="mb-6">
        <a href="{{ route('dlh.dashboard') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700 flex items-center gap-1.5 transition-colors">
            ← Kembali ke Dashboard
        </a>
    </div>

    {{-- Header Profil Unit --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="p-4 bg-emerald-50 text-emerald-700 rounded-2xl text-2xl border border-emerald-100">
                    🏢
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $bankSampah->nama }}</h1>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-xs font-bold rounded-full {{ $bankSampah->status == 'pending' ? 'text-amber-700 bg-amber-50 border border-amber-200' : 'text-emerald-700 bg-emerald-50 border border-emerald-200' }}">
                            {{ strtoupper($bankSampah->status) }}
                        </span>
                    </div>
                    <p class="text-sm text-slate-500 mt-1">Ditambahkan pada {{ $bankSampah->created_at ? $bankSampah->created_at->format('d M Y, H:i') : '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm mb-8">
        {{-- Detail Informasi Pokok --}}
        <div class="md:col-span-2 space-y-6">
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">📂 Dokumen & Informasi Legalitas</h3>
                
                <div class="grid grid-cols-1 gap-4 text-sm">
                    <div>
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Alamat Operasional</span>
                        <span class="text-slate-600 mt-0.5 block leading-relaxed">{{ $bankSampah->alamat }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Deskripsi / Catatan Tambahan</span>
                        <span class="text-slate-600 mt-0.5 block italic leading-relaxed">
                            {{ $bankSampah->deskripsi ?? 'Tidak ada catatan tambahan dari pemohon.' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection