<?php

namespace App\Http\Controllers;

use App\Models\Operasional;
use Illuminate\Http\Request;

class OperasionalController extends Controller
{
    public function index(Request $request)
    {
        $query = Operasional::query();

        // Pencarian berdasarkan kategori
        if ($request->search) {
            $query->where(
                'kategori',
                'like',
                '%' . $request->search . '%'
            );
        }

        // Filter bulan
        if ($request->bulan) {

            $query->whereMonth(
                'tanggal',
                date('m', strtotime($request->bulan))
            );

            $query->whereYear(
                'tanggal',
                date('Y', strtotime($request->bulan))
            );
        }

        $operasional = $query
            ->latest('tanggal')
            ->paginate(10);

        // Total pemasukan dan pengeluaran 
        $pemasukanQuery = clone $query;
        $pengeluaranQuery = clone $query;

        $totalPemasukan = $pemasukanQuery
            ->where('jenis_transaksi','Pemasukan')
            ->sum('total');

        $totalPengeluaran = $pengeluaranQuery
            ->where('jenis_transaksi','Pengeluaran')
            ->sum('total');

        $saldo = $totalPemasukan - $totalPengeluaran;

        // Saldo
        $saldo = $totalPemasukan - $totalPengeluaran;

        // Persentase
        $total = $totalPemasukan + $totalPengeluaran;

        $persenPemasukan =
            $total == 0 ? 0 : ($totalPemasukan / $total) * 100;

        $persenPengeluaran =
            $total == 0 ? 0 : ($totalPengeluaran / $total) * 100;
            
        // Grafik bulanan
       $grafik = Operasional::query();

        if ($request->search) {

            $grafik->where(
                'kategori',
                'like',
                '%' . $request->search . '%'
            );

        }

        if ($request->bulan) {

            $grafik->whereMonth(
                'tanggal',
                date('m', strtotime($request->bulan))
            );

            $grafik->whereYear(
                'tanggal',
                date('Y', strtotime($request->bulan))
            );

        }

        $grafik = $grafik
            ->selectRaw("
                MONTH(tanggal) as bulan,

                SUM(CASE
                    WHEN jenis_transaksi='Pemasukan'
                    THEN total
                    ELSE 0
                END) as pemasukan,

                SUM(CASE
                    WHEN jenis_transaksi='Pengeluaran'
                    THEN total
                    ELSE 0
                END) as pengeluaran
            ")
            ->groupByRaw("MONTH(tanggal)")
            ->orderByRaw("MONTH(tanggal)")
            ->get();

        return view(
            'admin.Operasional.index',
            compact(
                'operasional',
                'totalPemasukan',
                'totalPengeluaran',
                'saldo',
                'persenPemasukan',
                'persenPengeluaran',
                'grafik'
            )
        );
    }

    public function create()
    {
        return view('admin.Operasional.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_transaksi' => 'required',
            'kategori' => 'required|string|max:100',
            'harga' => 'required|numeric|min:0',
            'jumlah' => 'required|numeric|min:1',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string'
        ]);

        Operasional::create([
            'jenis_transaksi' => $request->jenis_transaksi,
            'kategori' => $request->kategori,
            'harga' => $request->harga,
            'jumlah' => $request->jumlah,
            'total' => $request->harga * $request->jumlah,
            'keterangan' => $request->keterangan,
            'sumber' => 'Manual',
            'tanggal' => $request->tanggal
        ]);

        return redirect()
            ->route('Operasional.index')
            ->with('success', 'Data operasional berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $operasional = Operasional::findOrFail($id);

        return view(
            'admin.Operasional.edit',
            compact('operasional')
        );
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'jenis_transaksi' => 'required',
            'kategori' => 'required|string|max:100',
            'harga' => 'required|numeric|min:0',
            'jumlah' => 'required|numeric|min:1',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string'
        ]);

        $operasional = Operasional::findOrFail($id);

        $operasional->update([
            'jenis_transaksi' => $request->jenis_transaksi,
            'kategori' => $request->kategori,
            'harga' => $request->harga,
            'jumlah' => $request->jumlah,
            'total' => $request->harga * $request->jumlah,
            'keterangan' => $request->keterangan,
            'tanggal' => $request->tanggal
        ]);

        return redirect()
            ->route('Operasional.index')
            ->with('success', 'Data operasional berhasil diubah.');
    }

    public function destroy($id)
    {
        Operasional::findOrFail($id)->delete();

        return redirect()
            ->back()
            ->with('success', 'Data operasional berhasil dihapus.');
    }
}