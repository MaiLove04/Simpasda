<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;

use Illuminate\Support\Facades\Auth;

class NasabahWebController extends Controller
{

    public function index()
    {

        $nasabahs =

            User::where(

                'role',

                'nasabah'

            )

            ->where(

                'bank_sampah_id',

                Auth::user()
                    ->bank_sampah_id

            )

            ->get();



        return view(

            'admin.nasabah.index',

            compact(
                'nasabahs'
            )
        );
    }




    public function show($id)
    {

        $nasabah =

            User::findOrFail(
                $id
            );



        return view(

            'admin.nasabah.show',

            compact(
                'nasabah'
            )
        );
    }




    public function approve($id)
    {

        $nasabah =

            User::findOrFail(
                $id
            );



        $nasabah->update([

            'status' =>
                'aktif'
        ]);



        return redirect(
            '/admin/nasabah'
        );
    }




    public function destroy($id)
    {

        $nasabah =

            User::findOrFail(
                $id
            );



        $nasabah->delete();



        return redirect(
            '/admin/nasabah'
        );
    }
    
    public function updateStatus(
    Request $request,
    $id
) {

    $request->validate([

        'status' =>
            'required'
    ]);


    $nasabah =

        User::findOrFail(
            $id
        );


    $nasabah->update([

        'status' =>
            $request->status
    ]);


    return redirect(
        '/admin/nasabah'
    )->with(
        'success',
        'Status berhasil diupdate'
    );
}

}