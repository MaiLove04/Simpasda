<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BankSampahController extends Controller
{
    public function index()
    {
        $banks = \App\Models\BankSampah::where(
            'status',
            'active'
        )->get();

        return response()->json([
            'data' => $banks
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string',
            'alamat' => 'required|string'
        ]);

        $bank = \App\Models\BankSampah::create([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'status' => 'active'
        ]);

        return response()->json([
            'message' => 'Bank sampah berhasil dibuat',
            'data' => $bank
        ]);
    }
    public function approve($id)
    {
        $bank = \App\Models\BankSampah::findOrFail($id);

        $bank->status = 'active';
        $bank->save();

        return response()->json([
            'message' => 'Bank sampah disetujui',
            'data' => $bank
        ]);
    }

    public function show($id)
    {
        $bank = \App\Models\BankSampah::findOrFail($id);

        return response()->json($bank);
    }
    public function destroy($id)
    {
        $bank = \App\Models\BankSampah::findOrFail($id);
        $bank->delete();

        return response()->json([
            'message' => 'Bank sampah dihapus'
        ]);
    }

}
