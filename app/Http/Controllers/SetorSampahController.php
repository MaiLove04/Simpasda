<?php

namespace App\Http\Controllers;

use App\Models\SetorSampah;
use Illuminate\Http\Request;

class SetorSampahController extends Controller
{
    // Method untuk menyimpan data setor sampah
    public function index()
    {
        $data =

            SetorSampah::with([

                'jenisSampah',

                'details.jenisSampah',

            ])

            ->latest()

            ->get();


        return response()->json([

            'data' => $data,
        ]);
    }

   //store
   public function store(
    Request $request
    )
        {

            $request->validate([

                'user_id' =>

                    'required|exists:users,id',


                'catatan' =>

                    'nullable|string',


                'items' =>

                    'required|array',


                'items.*.jenis_sampah_id' =>

                    'required|exists:jenis_sampahs,id',


                'items.*.berat' =>

                    'nullable|numeric',
            ]);



            $setor =
                SetorSampah::create([

                    'user_id' =>

                        $request->user_id,


                    'catatan' =>

                        $request->catatan,


                    'status' =>

                        'Menunggu konfirmasi kurir',
                ]);



            foreach (
                $request->items
                as $item
            ) {

                $setor
                    ->details()
                    ->create([


                        'jenis_sampah_id' =>

                            $item[
                                'jenis_sampah_id'
                            ],


                        'berat' =>

                            $item[
                                'berat'
                            ] ?? null,


                        'harga_per_kg' =>

                            0,


                        'subtotal' =>

                            0,
                    ]);
            }



            return response()->json([

                'message' =>

                    'Berhasil',


                'data' =>

                    $setor
                        ->load(
                            'details'
                        ),
            ]);
        }
}