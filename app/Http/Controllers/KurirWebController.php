<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KurirWebController extends Controller
{
    /**
     * Menampilkan daftar kurir.
     */
    public function index()
    {
        $kurirs = User::where('role', 'kurir')
            ->where('bank_sampah_id', Auth::user()->bank_sampah_id)
            ->latest()
            ->paginate(10);

        return view('admin.kurir.index', compact('kurirs'));
    }

    /**
     * Form tambah kurir.
     */
    public function create()
    {
        return view('admin.kurir.create');
    }

    /**
     * Simpan data kurir baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'alamat' => 'required',
            'no_hp' => 'required|unique:users',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $foto = null;

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');

            $fileName = time() . '_' . $file->getClientOriginalName();

            $file->move(public_path('uploads/users'), $fileName);

            $foto = 'uploads/users/' . $fileName;
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'alamat' => $request->alamat,
            'no_hp' => $request->no_hp,
            'foto' => $foto,
            'role' => 'kurir',
            'status' => 'aktif',
            'bank_sampah_id' => Auth::user()->bank_sampah_id,
        ]);

        return redirect('/admin/kurir')
            ->with('success', 'Data kurir berhasil ditambahkan.');
    }

    /**
     * Form edit kurir.
     */
    public function edit($id)
    {
        $kurir = User::findOrFail($id);

        return view('admin.kurir.edit', compact('kurir'));
    }

    /**
     * Update data kurir.
     */
    public function update(Request $request, $id)
    {
        $kurir = User::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $kurir->id,
            'alamat' => 'required',
            'no_hp' => 'required',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $foto = $kurir->foto;

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');

            $fileName = time() . '_' . $file->getClientOriginalName();

            $file->move(public_path('uploads/users'), $fileName);

            $foto = 'uploads/users/' . $fileName;
        }

        $kurir->update([
            'name' => $request->name,
            'email' => $request->email,
            'alamat' => $request->alamat,
            'no_hp' => $request->no_hp,
            'foto' => $foto,
        ]);

        return redirect('/admin/kurir')
            ->with('success', 'Data kurir berhasil diperbarui.');
    }

    /**
     * Hapus data kurir.
     */
    public function destroy($id)
    {
        $kurir = User::findOrFail($id);

        $kurir->delete();

        return redirect('/admin/kurir')
            ->with('success', 'Data kurir berhasil dihapus.');
    }
}