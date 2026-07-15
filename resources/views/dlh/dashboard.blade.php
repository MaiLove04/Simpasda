@extends('layouts.dlh')

@section('title', 'Dashboard Eksekutif DLH - Simpasda')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
    
    {{-- Header Ringkasan --}}
    <div class="mb-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 border-b border-slate-200 pb-6">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-emerald-700 uppercase tracking-wider bg-emerald-50 w-fit px-2.5 py-1 rounded-md mb-2">
                Otoritas Wilayah Kerja DLH
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
                Ringkasan Eksekutif Daerah
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Sistem pemantauan real-time ekosistem sampah terintegrasi. Selamat bekerja, 
                <span class="font-semibold text-slate-700">{{ Auth::user()->name ?? 'Petugas DLH' }}</span>.
            </p>
        </div>
        <div class="flex items-center gap-3 bg-white p-3 rounded-xl border border-slate-200 shadow-sm w-fit">
            <span class="flex h-3 w-3 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
            </span>
            <div class="text-left">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide leading-none">Status Sinkronisasi</p>
                <p class="text-xs font-semibold text-slate-700 mt-0.5">Semua Unit Terhubung</p>
            </div>
        </div>
    </div>

    {{-- Kartu Atas - Statistik Utama --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all duration-200">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-slate-400 tracking-wider uppercase">Fasilitas Daerah</span>
                <span class="p-2.5 bg-slate-50 text-slate-700 rounded-xl text-lg font-medium border border-slate-100">🏢</span>
            </div>
            <h3 class="text-slate-500 font-medium text-sm">Total Bank Sampah</h3>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $totalBankSampah }}</span>
                <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded">Unit</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all duration-200">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-slate-400 tracking-wider uppercase">Sirkulasi Demografi</span>
                <span class="p-2.5 bg-slate-50 text-slate-700 rounded-xl text-lg font-medium border border-slate-100">👥</span>
            </div>
            <h3 class="text-slate-500 font-medium text-sm">Nasabah Aktif Global</h3>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $totalNasabah }}</span>
                <span class="text-xs font-medium text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded">Jiwa</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all duration-200 ring-2 ring-rose-500/10 bg-gradient-to-br from-white to-rose-50/10">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-rose-500 tracking-wider uppercase">Respon Layanan</span>
                <span class="p-2.5 bg-rose-50 text-rose-600 rounded-xl text-lg font-medium border border-rose-100">⚠️</span>
            </div>
            <h3 class="text-slate-600 font-medium text-sm">Aduan Menunggu Tindakan</h3>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-3xl font-extrabold text-rose-600 tracking-tight">{{ $aduanBaru }}</span>
                <span class="text-xs font-bold text-rose-700 bg-rose-100/60 px-2 py-0.5 rounded {{ $aduanBaru > 0 ? 'animate-pulse' : '' }}">
                    {{ $aduanBaru > 0 ? 'Butuh Respon' : 'Bersih' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Main Layout Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        {{-- Kolom Kiri: Tabel Registrasi & Tiket Aduan --}}
        <div class="lg:col-span-2 space-y-8">
            
            {{-- Tabel Validasi Bank Sampah Pending --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between bg-slate-50/50 gap-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Validasi Pendaftaran Unit Baru</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Izin operasional unit bank sampah yang membutuhkan verifikasi legalitas berkas.</p>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-[11px] font-bold uppercase text-slate-400 border-b border-slate-200">
                            <tr>
                                <th scope="col" class="px-6 py-3.5">Nama Unit/Instansi</th>
                                <th scope="col" class="px-6 py-3.5">Wilayah Kerja / Alamat</th>
                                <th scope="col" class="px-6 py-3.5 text-center">Status Berkas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($bankSampahPending as $bank)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-slate-900">{{ $bank->nama }}</td>
                                    <td class="px-6 py-4 text-slate-500 text-xs">{{ $bank->alamat }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold text-amber-700 bg-amber-50 rounded-lg border border-amber-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            {{ strtoupper($bank->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-10 text-center text-slate-400">
                                        <div class="flex flex-col items-center justify-center gap-2 py-4">
                                            <span class="text-2xl">🍃</span>
                                            <p class="text-sm font-semibold text-slate-600">Seluruh permohonan baru telah divalidasi</p>
                                            <p class="text-xs text-slate-400">Tidak ada pengajuan berkas berstatus tertunda.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Komponen Aduan Masuk Real-Time --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Tiket Aduan Masuk Terbaru</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Laporan kendala operasional lapangan dari nasabah dan internal karyawan DLH.</p>
                    </div>
                    <a href="{{ route('dlh.aduan.index') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-100 transition-colors">
                        Lihat Semua Tiket →
                    </a>
                </div>
                <div class="px-6 py-2 text-center text-[11px] text-slate-400 border-b border-slate-100 bg-slate-50/20 italic">
                    Gunakan Menu "Kelola Aduan" di navbar atas untuk memperbarui status tindakan aduan.
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($daftarAduanTerbaru as $aduan)
                        <div class="p-5 flex justify-between items-start hover:bg-slate-50/50 transition-colors gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-[9px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-600 uppercase tracking-wide border border-slate-200">
                                        {{ $aduan->kategori ?? 'Aduan Umum' }}
                                    </span>
                                    <span class="text-[10px] text-slate-400">
                                        {{ $aduan->created_at ? $aduan->created_at->diffForHumans() : '' }}
                                    </span>
                                </div>
                                <h4 class="text-sm font-semibold text-slate-900 mt-1">{{ $aduan->judul }}</h4>
                                <p class="text-xs text-slate-500 line-clamp-2">{{ $aduan->deskripsi }}</p>
                                <p class="text-[10px] text-slate-400 pt-1">Pelapor: <span class="font-medium text-slate-600">{{ $aduan->pelapor ?? 'Anonim' }}</span></p>
                            </div>
                            
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $aduan->status == 'menunggu' ? 'bg-amber-50 text-amber-700 border border-amber-100' : 'bg-blue-50 text-blue-700 border border-blue-100' }}">
                                {{ strtoupper($aduan->status) }}
                            </span>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400 text-sm italic">
                            👍 Bagus! Tidak ada tiket aduan aktif yang tertunda saat ini.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- Kolom Kanan: Sidebar Komoditas --}}
        <div class="space-y-6">
            
            {{-- Komoditas Wilayah Dinamis --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <span>📊</span> Komoditas Wilayah (Database)
                </h3>
                <div class="space-y-4">
                    {{-- Kategori Organik --}}
                    <div>
                        <div class="flex justify-between text-xs font-semibold text-slate-600 mb-1">
                            <span>Organik / Kompos</span>
                            <span class="text-slate-400">{{ $persenOrganik }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-emerald-600 h-full rounded-full transition-all duration-500" style="width: {{ $persenOrganik }}%"></div>
                        </div>
                    </div>
                    
                    {{-- Kategori Anorganik --}}
                    <div>
                        <div class="flex justify-between text-xs font-semibold text-slate-600 mb-1">
                            <span>Anorganik (Plastik/Logam)</span>
                            <span class="text-slate-400">{{ $persenAnorganik }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-teal-600 h-full rounded-full transition-all duration-500" style="width: {{ $persenAnorganik }}%"></div>
                        </div>
                    </div>
                    
                    {{-- Kategori Kertas --}}
                    <div>
                        <div class="flex justify-between text-xs font-semibold text-slate-600 mb-1">
                            <span>Kertas & Karton</span>
                            <span class="text-slate-400">{{ $persenKertas }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-amber-500 h-full rounded-full transition-all duration-500" style="width: {{ $persenKertas }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Versi Aplikasi --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm text-[11px] text-slate-400 flex justify-between items-center bg-slate-50/50">
                <span>Versi Aplikasi Simpasda</span>
                <span class="font-mono text-slate-600 bg-slate-200/60 px-2 py-0.5 rounded">v2.1-Stable</span>
            </div>

        </div>

    </div>

</div>
@endsection