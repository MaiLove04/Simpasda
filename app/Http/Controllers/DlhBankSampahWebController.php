<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\BankSampah;
use App\Models\SetorSampah;  
use Illuminate\Support\Facades\DB;

class DlhBankSampahWebController extends Controller
{


    public function index()
    {
        $bankSampahs = BankSampah::with('users')->paginate(10);

        foreach ($bankSampahs as $bank) {
            $bank->total_sampah = SetorSampah::whereIn(
                'user_id',
                $bank->users->pluck('id')
            )
            ->where('status', 'selesai')
            ->sum('berat');
        }
    
        return view('dlh.bank-sampah.index', compact('bankSampahs'));
    }

    public function create()
    {
        return view('dlh.bank-sampah.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'alamat' => 'required',
            'email' => 'required|email|unique:users,email',
        ]);

        $bankSampah = BankSampah::create([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'status' => 'active'
        ]);

        User::create([
            'name' => $request->nama,
            'email' => $request->email,
            'password' => bcrypt('password123'),
            'role' => 'admin_bank_sampah',
            'status' => 'aktif',
            'alamat' => $request->alamat,
            'bank_sampah_id' => $bankSampah->id,
        ]);

        return redirect()
            ->route('dlh.bank-sampah.index')
            ->with('success', 'Bank Sampah berhasil ditambahkan.');
    }

    public function show($id)
    {
        $bankSampah = BankSampah::findOrFail($id);
        //Total Sampah
        $totalSampah = SetorSampah::where('status', 'selesai')->sum('berat');

        return view('dlh.bank-sampah.show', compact('bankSampah','totalSampah'));
    }

        public function edit($id)
    {
        $bank = BankSampah::findOrFail($id);

        return view('dlh.bank-sampah.edit', compact('bank'));
    }

   public function update(Request $request, $id)
    {
        $request->validate([
            'nama_bank_sampah' => 'required',
            'jumlah_nasabah' => 'nullable|integer',
            'alamat' => 'required',
            'total_sampah' => 'nullable|numeric',
            'status' => 'required'
        ]);

        $bank = BankSampah::findOrFail($id);

        $bank->update([
            'nama_bank_sampah' => $request->nama_bank_sampah,
            'jumlah_nasabah' => $request->jumlah_nasabah,
            'alamat' => $request->alamat,
            'total_sampah' => $request->total_sampah,
            'status' => $request->status
        ]);

        return redirect()
            ->route('dlh.bank-sampah.index')
            ->with('success', 'Data bank sampah berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $bankSampah = BankSampah::findOrFail($id);

        User::where(
            'bank_sampah_id',
            $bankSampah->id
        )->delete();

        $bankSampah->delete();

        return back()->with(
            'success',
            'Bank Sampah berhasil dihapus.'
        );
    }

    public function approve($id)
    {
        $bankSampah = BankSampah::findOrFail($id);

        $bankSampah->update([
            'status' => 'active'
        ]);

        return back()->with(
            'success',
            'Bank Sampah berhasil disetujui.'
        );
    }
}