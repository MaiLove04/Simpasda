@extends('layouts.dlh')

@section('title', 'Kelola Pengaduan Warga - SIMPASDA')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
    
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pusat Aduan & Kendala Warga</h1>
            <p class="text-sm text-slate-500 mt-1">Pantau, verifikasi, dan tindak lanjuti laporan fasilitas atau kendala sampah dari masyarakat.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-lg text-emerald-800 text-sm font-medium flex items-center gap-2">
            <span>✅</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <h3 class="font-bold text-slate-700 text-sm uppercase tracking-wider">Daftar Laporan Masuk</h3>
            <span class="text-xs bg-slate-200/70 text-slate-600 px-2.5 py-1 rounded-full font-medium">Data Realtime</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-100/50 text-slate-600 text-xs font-bold uppercase tracking-wider">
                        <th class="py-4 px-6 w-16 text-center">No</th>
                        <th class="py-4 px-6 w-48">Pengirim</th>
                        <th class="py-4 px-6 w-44">Kategori</th>
                        <th class="py-4 px-6">Isi Aduan & Tanggapan DLH</th>
                        <th class="py-4 px-6 w-32 text-center">Status</th>
                        <th class="py-4 px-6 w-64 text-center">Aksi Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse($aduans as $index => $aduan)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-4 px-6 text-center font-medium text-slate-400">
                                {{ ($aduans->currentPage() - 1) * $aduans->perPage() + $index + 1 }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-semibold text-slate-900">{{ $aduan->user->name ?? 'User ID: '.$aduan->user_id }}</div>
                                <div class="text-[11px] text-emerald-600 mt-0.5 uppercase font-bold tracking-wider bg-emerald-50 inline-block px-2 py-0.5 rounded">
                                    {{ $aduan->role_pengirim }}
                                </div>
                            </td>
                            <td class="py-4 px-6 font-medium text-slate-900">
                                {{ $aduan->kategori_aduan }}
                            </td>
                            <td class="py-4 px-6">
                                <p class="text-slate-700 font-medium mb-1">{{ $aduan->isi_aduan }}</p>
                                
                                @if($aduan->foto_bukti)
                                    <div class="mb-2">
                                        <a href="{{ asset($aduan->foto_bukti) }}" target="_blank" class="inline-flex items-center text-xs text-blue-600 font-medium hover:underline gap-1">
                                            🖼️ Lihat Foto Bukti
                                        </a>
                                    </div>
                                @endif

                                @if($aduan->tanggapan)
                                    <div class="mt-2 text-xs bg-slate-50 border border-slate-200 p-2.5 rounded-lg text-slate-600">
                                        <span class="font-bold text-slate-700 block mb-0.5">📢 Tanggapan DLH:</span>
                                        {{ $aduan->tanggapan }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if($aduan->status === 'menunggu')
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200/60">
                                        Menunggu
                                    </span>
                                @elseif($aduan->status === 'diproses')
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/60">
                                        Diproses
                                    </span>
                                @elseif($aduan->status === 'selesai')
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                        Selesai
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                <form action="{{ route('dlh.aduan.update', $aduan->id) }}" method="POST" class="space-y-2">
                                    @csrf
                                    @method('PUT')
                                    
                                    @if($aduan->status !== 'selesai')
                                        <textarea name="tanggapan" rows="2" class="w-full text-xs p-2 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-600" placeholder="Tulis tanggapan tindakan..."></textarea>
                                        
                                        <div class="flex gap-1.5 justify-center">
                                            @if($aduan->status === 'menunggu')
                                                <button type="submit" name="status" value="diproses" class="w-full text-[11px] font-bold bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white border border-blue-200 py-1.5 rounded-md transition-all shadow-sm">
                                                    Proses
                                                </button>
                                            @endif
                                            <button type="submit" name="status" value="selesai" class="w-full text-[11px] font-bold bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white border border-emerald-200 py-1.5 rounded-md transition-all shadow-sm">
                                                Selesai
                                            </button>
                                        </div>
                                    @else
                                        <div class="text-center text-xs text-slate-400 font-medium italic py-2">
                                            Aduan Selesai Ditangani
                                        </div>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <div class="text-3xl mb-2">📥</div>
                                <div class="text-slate-400 font-medium text-sm">Belum ada aduan atau laporan masuk dari aplikasi mobile.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
            {{ $aduans->links() }}
        </div>
    </div>

</div>
@endsection