<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;

class KurirWebController extends Controller
{
    /**
     * Menampilkan halaman daftar kurir.
     */
    public function index()
    {
        // Ambil kurir yang terikat pada bank sampah admin yang sedang login
        $kurirs = User::where('role', 'kurir')
            ->where('bank_sampah_id', Auth::user()->bank_sampah_id)
            ->latest()
            ->get();

        return view('admin.kurir.index', compact('kurirs'));
    }

    /**
     * Menampilkan form untuk membuat kurir baru.
     */
    public function create()
    {
        return view('admin.kurir.create');
    }

    /**
     * Menyimpan data kurir baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'no_hp' => 'required|string|max:15',
            'alamat' => 'required|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $pathFoto = null;
        if ($request->hasFile('foto')) {
            // Simpan file ke public/storage/foto-kurir dan dapatkan path-nya
            $pathFoto = $request->file('foto')->store('foto-kurir', 'public');
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'no_hp' => $request->no_hp,
            'alamat' => $request->alamat,
            'foto' => $pathFoto,
            'role' => 'kurir',
            'status' => 'approved', // Langsung aktif
            'bank_sampah_id' => Auth::user()->bank_sampah_id, // Ikat ke bank sampah admin
        ]);

        return redirect('/admin/kurir')->with('success', 'Kurir baru berhasil ditambahkan!');
    }

    /**
     * Menampilkan form untuk mengedit data kurir.
     */
    public function edit(User $kurir)
    {
        // Keamanan: Pastikan admin hanya bisa edit kurir di bank sampahnya sendiri
        if ($kurir->bank_sampah_id !== Auth::user()->bank_sampah_id || $kurir->role !== 'kurir') {
            abort(403, 'Akses ditolak.');
        }

        return view('admin.kurir.edit', compact('kurir'));
    }

    /**
     * Memperbarui data kurir di database.
     */
    public function update(Request $request, User $kurir)
    {
        // Keamanan: Pastikan admin hanya bisa update kurir di bank sampahnya sendiri
        if ($kurir->bank_sampah_id !== Auth::user()->bank_sampah_id || $kurir->role !== 'kurir') {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $kurir->id,
            'no_hp' => 'required|string|max:15',
            'alamat' => 'required|string',
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $dataToUpdate = [
            'name' => $request->name,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'alamat' => $request->alamat,
        ];

        // Jika admin mengisi password baru, hash dan tambahkan ke data update
        if ($request->filled('password')) {
            $dataToUpdate['password'] = Hash::make($request->password);
        }

        $kurir->update($dataToUpdate);

        return redirect('/admin/kurir')->with('success', 'Data kurir berhasil diperbarui!');
    }

    /**
     * Menghapus data kurir dari database.
     */
    public function destroy(User $kurir)
    {
        // Keamanan: Pastikan admin hanya bisa hapus kurir di bank sampahnya sendiri
        if ($kurir->bank_sampah_id !== Auth::user()->bank_sampah_id || $kurir->role !== 'kurir') {
            abort(403, 'Akses ditolak.');
        }

        // Hapus foto lama jika ada
        if ($kurir->foto) {
            Storage::disk('public')->delete($kurir->foto);
        }

        $kurir->delete();

        return redirect('/admin/kurir')->with('success', 'Data kurir berhasil dihapus!');
    }
}