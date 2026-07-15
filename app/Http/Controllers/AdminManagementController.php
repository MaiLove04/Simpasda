<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminManagementController extends Controller
{
    /**
     * Daftar Admin Bank Sampah
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'admin_bank')
            ->where('bank_sampah_id', Auth::user()->bank_sampah_id);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $admins = $query->orderBy('name')->paginate(10);

        return view('admin.kelola-admin.index', compact('admins'));
    }

    /**
     * Form Tambah Admin
     */
    public function create()
    {
        return view('admin.kelola-admin.create');
    }

    /**
     * Simpan Admin Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'no_hp' => 'nullable|max:20',
            'alamat' => 'nullable'
        ]);

        User::create([
            'name'            => $request->name,
            'email'           => $request->email,
            'password'        => Hash::make($request->password),
            'no_hp'           => $request->no_hp,
            'alamat'          => $request->alamat,
            'role'            => 'admin_bank',
            'status'          => 'aktif',
            'bank_sampah_id'  => Auth::user()->bank_sampah_id,
        ]);

        return redirect()
            ->route('kelola-admin.index')
            ->with('success', 'Admin berhasil ditambahkan.');
    }

    /**
     * Detail Admin
     */
    public function edit($id)
    {
        $admin = User::where('role','admin_bank')
            ->where('bank_sampah_id',Auth::user()->bank_sampah_id)
            ->findOrFail($id);

        return view('admin.kelola-admin.edit', compact('admin'));
    }

    /**
     * Update Admin
     */
    public function update(Request $request, $id)
    {
        $admin = User::findOrFail($id);

        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email,' . $admin->id,
            'no_hp' => 'nullable|max:20',
            'alamat' => 'nullable',
        ]);

        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->no_hp = $request->no_hp;
        $admin->alamat = $request->alamat;

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'min:6|confirmed'
            ]);

            $admin->password = Hash::make($request->password);
        }

        $admin->save();

        return redirect()
            ->route('kelola-admin.index')
            ->with('success', 'Admin berhasil diperbarui.');
    }

    /**
     * Aktif / Nonaktif
     */
    public function toggleStatus(Request $request, $id)
    {
        $admin = User::findOrFail($id);

        // Tidak boleh menonaktifkan akun sendiri
        if ($admin->id == Auth::id()) {
            return back()->with('error', 'Anda tidak dapat mengubah status akun sendiri.');
        }

        $request->validate([
            'status' => 'required|in:aktif,nonaktif'
        ]);

        $admin->status = $request->status;
        $admin->save();

        return back()->with('success', 'Status admin berhasil diperbarui.');
    }

    public function show($id)
    {
        $admin = User::where('role', 'admin_bank')
            ->where('bank_sampah_id', Auth::user()->bank_sampah_id)
            ->findOrFail($id);

        return response()->json($admin);
    }
}