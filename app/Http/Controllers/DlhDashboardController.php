<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BankSampah;
use App\Models\User;
use App\Models\Aduan;

class DlhDashboardController extends Controller
{
    public function index()
    {
        $totalBankSampah = BankSampah::count();
        $totalNasabah = User::where('role', 'nasabah')->count();
        $aduanBaru = Aduan::where('status', 'menunggu')->count();

        $bankSampahPending = BankSampah::where('status', 'pending')->latest()->take(5)->get();

        return view('dlh.dashboard', compact('totalBankSampah', 'totalNasabah', 'aduanBaru', 'bankSampahPending'));
    }
}