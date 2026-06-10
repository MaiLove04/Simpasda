<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BankSampah;

class DlhBankSampahWebController extends Controller
{
    public function index()
    {
        $bankSampahs = BankSampah::latest()->paginate(10);
        return view('dlh.bank-sampah.index', compact('bankSampahs'));
    }

    public function approve($id)
    {
        $bank = BankSampah::findOrFail($id);
        $bank->update(['status' => 'active']); 

        return back()->with('success', 'Cabang Bank Sampah berhasil disetujui dan diaktifkan!');
    }

    public function destroy($id)
    {
        $bank = BankSampah::findOrFail($id);
        $bank->delete();
        return back()->with('success', 'Data Bank Sampah berhasil dihapus.');
    }
}