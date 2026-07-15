<?php

namespace App\Http\Controllers;

use App\Models\Mitra;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class MitraController extends Controller
{
    public function index(Request $request)
{
    $query = Mitra::query();

    // Pencarian
    if ($request->search) {
        $query->where(
            'nama_mitra',
            'like',
            '%' . $request->search . '%'
        );
    }

    // Filter status
    if ($request->status) {
        $query->where(
            'status',
            $request->status
        );
    }

    $mitra = $query
        ->latest()
        ->paginate(10)
        ->withQueryString();

    // Statistik
    $totalMitra = Mitra::count();

    $mitraAktif = Mitra::where('status', 'Aktif')->count();

    $mitraTidakAktif = Mitra::where('status', 'Tidak Aktif')->count();

    return view(
        'admin.mitra.index',
        compact(
            'mitra',
            'totalMitra',
            'mitraAktif',
            'mitraTidakAktif'
        )
    );
}


    public function create()
    {
        return view('admin.mitra.create');
    }

    // kita isi nanti
    public function store(Request $request)
    {
        $request->validate([

            'nama_mitra' => 'required|string|max:255',

            'jenis_mitra' => 'required',

            'penanggung_jawab' => 'required|string|max:255',

            'no_hp' => 'required|string|max:20',

            'email' => 'nullable|email|max:255',

            'alamat' => 'required',

            'status' => 'required',

            'keterangan' => 'nullable'

        ]);

        Mitra::create([

            'nama_mitra' => $request->nama_mitra,

            'jenis_mitra' => $request->jenis_mitra,

            'penanggung_jawab' => $request->penanggung_jawab,

            'no_hp' => $request->no_hp,

            'email' => $request->email,

            'alamat' => $request->alamat,

            'status' => $request->status,

            'keterangan' => $request->keterangan

        ]);

        return redirect()
            ->route('Mitra.index')
            ->with(
                'success',
                'Data mitra berhasil ditambahkan.'
            );
    }

    public function show($id)
    {
        $mitra = Mitra::with(['pengiriman','user'])->findOrFail($id);

        $totalPengiriman = $mitra->pengiriman()->count();

        $belumLunas = $mitra->pengiriman()
            ->where('status_pembayaran', '!=', 'Lunas')
            ->count();

        $lunas = $mitra->pengiriman()
            ->where('status_pembayaran', 'Lunas')
            ->count();

        $totalPendapatan = $mitra->pengiriman()
            ->where('status_pembayaran', 'Lunas')
            ->sum('total');

        return view(
            'admin.mitra.show',
            compact(
                'mitra',
                'totalPengiriman',
                'belumLunas',
                'lunas',
                'totalPendapatan'
            )
        );
    }

    public function edit($id)
    {
        $mitra = Mitra::findOrFail($id);

        return view(
            'admin.mitra.edit',
            compact('mitra')
        );
    }

    public function update(Request $request, $id)
    {
        $request->validate([

            'nama_mitra' => 'required|string|max:255',

            'jenis_mitra' => 'required',

            'penanggung_jawab' => 'required|string|max:255',

            'no_hp' => 'required|string|max:20',

            'email' => 'nullable|email|max:255',

            'alamat' => 'required',

            'status' => 'required',

            'keterangan' => 'nullable'

        ]);

        $mitra = Mitra::findOrFail($id);

        $mitra->update([

            'nama_mitra' => $request->nama_mitra,

            'jenis_mitra' => $request->jenis_mitra,

            'penanggung_jawab' => $request->penanggung_jawab,

            'no_hp' => $request->no_hp,

            'email' => $request->email,

            'alamat' => $request->alamat,

            'status' => $request->status,

            'keterangan' => $request->keterangan

        ]);

        return redirect()
            ->route('Mitra.index')
            ->with(
                'success',
                'Data mitra berhasil diubah.'
            );
    }

    public function buatAkun($id)
    {
        $mitra = Mitra::findOrFail($id);

        // Cek apakah mitra sudah punya akun
        if (User::where('mitra_id', $mitra->id)->exists()) {
            return back()->with('error', 'Mitra sudah memiliki akun.');
        }

        User::create([
            'name'            => $mitra->nama_mitra,
            'email'           => $mitra->email,
            'password'        => Hash::make('12345678'), // password awal
            'role'            => 'mitra',
            'status'          => 'aktif',

            // INI YANG PENTING
            'mitra_id'        => $mitra->id,

            // optional
            'no_hp'           => $mitra->no_hp,
            'alamat'          => $mitra->alamat,
            'bank_sampah_id'  => auth()->user()->bank_sampah_id,
        ]);

        return back()->with(
            'success',
            'Akun mitra berhasil dibuat. Password awal: 12345678'
        );
    }

    public function destroy($id)
    {
        $mitra = Mitra::findOrFail($id);

        // hapus akun login mitra jika ada
        User::where('mitra_id', $mitra->id)->delete();

        // hapus data mitra
        $mitra->delete();

        return redirect()
            ->back()
            ->with('success','Data mitra berhasil dihapus.');
    }

   public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ], [
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ]);

        $mitra = Mitra::findOrFail($id);

        $user = User::where('mitra_id', $mitra->id)->first();

        if (!$user) {
            return back()->with(
                'error',
                'Akun mitra belum tersedia.'
            );
        }

        // Mencegah password baru sama dengan password lama
        if (Hash::check($request->password, $user->password)) {
            return back()
                ->withErrors([
                    'password' => 'Password baru tidak boleh sama dengan password lama.'
                ])
                ->withInput();
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with(
            'success',
            'Password berhasil diperbarui.'
        );
    }
}

