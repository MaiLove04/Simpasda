<?php

namespace App\Http\Controllers;

use App\Models\JenisSampah;
use Illuminate\Http\Request;

class JenisSampahWebController extends Controller
{
    // ================= INDEX =================
    public function index(Request $request)
    {
        $query = JenisSampah::query();

        if ($request->status && $request->status != 'semua') {
            $query->where('status', $request->status);
        }

        $jenisSampahs = $query->orderBy('nama')->get();
        $total = JenisSampah::count();
        $aktif = JenisSampah::where('status','aktif')->count();
        $nonaktif = JenisSampah::where('status','nonaktif')->count();

        return view(
            'admin.jenis_sampah.index',
            compact(
                'jenisSampahs',
                'total',
                'aktif',
                'nonaktif'
            )
        );

        return view(
            'admin.jenis_sampah.index',
            compact('jenisSampahs')
        );
    }
    // ================= CREATE =================
    public function create()
    {
        return view('admin.jenis_sampah.create');
    }

    // ================= STORE =================
    public function store(Request $request)
    {
        $request->validate([

            'nama' => 'required',

            // 'kode_icon' => 'required',

            'harga_per_kg' => 'required|numeric',

            //'poin_per_kg' => 'required|numeric',

            'status' => 'required',

        ]);

        JenisSampah::create([

            'bank_sampah_id' => auth()->user()->bank_sampah_id,

            'nama' => $request->nama,

            //'kode_icon' => $request->kode_icon,

            'harga_per_kg' => $request->harga_per_kg,

            //'poin_per_kg' => $request->poin_per_kg,

            'status' => $request->status,

        ]);

        return redirect('/admin/jenis-sampah')
            ->with('success', 'Jenis sampah berhasil ditambahkan');
    }

    // ================= EDIT =================
    public function edit($id)
    {
        $item = JenisSampah::findOrFail($id);

        return view(
            'admin.jenis_sampah.edit',
            compact('item')
        );
    }

    // ================= UPDATE =================
    public function update(Request $request, $id)
    {
        $item = JenisSampah::findOrFail($id);

        $item->update([

            'nama' => $request->nama,

           // 'kode_icon' => $request->kode_icon,

            'harga_per_kg' => $request->harga_per_kg,

            //'poin_per_kg' => $request->poin_per_kg,

            'status' => $request->status,

        ]);

        return redirect('/admin/jenis-sampah')
            ->with('success', 'Jenis sampah berhasil diupdate');
    }

    public function destroy($id)
    {
        $item = JenisSampah::findOrFail($id);

        $item->update([
            'status' => 'nonaktif'
        ]);

        return redirect('/admin/jenis-sampah')
            ->with('success', 'Jenis sampah berhasil dinonaktifkan');
    }

    public function toggleStatus($id)
    {
        $item = JenisSampah::findOrFail($id);

        $item->status = $item->status == 'aktif'
            ? 'nonaktif'
            : 'aktif';

        $item->save();

        return redirect('/admin/jenis-sampah')
            ->with('success', 'Status jenis sampah berhasil diperbarui.');
    }
}