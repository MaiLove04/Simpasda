<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;

use Illuminate\Support\Facades\Auth;

class KurirWebController extends Controller
{


    // ================= LIST =================
    public function index()
    {

        $kurirs =

            User::where(

                'role',

                'kurir'

            )

            ->where(

                'bank_sampah_id',

                Auth::user()
                    ->bank_sampah_id

            )

            ->get();



        return view(

            'admin.kurir.index',

            compact(
                'kurirs'
            )
        );
    }





    // ================= FORM CREATE =================
    public function create()
    {

        return view(

            'admin.kurir.create'
        );
    }





    // ================= STORE =================
    public function store(
        Request $request
    ) {

        $request->validate([

            'name' =>
                'required',


            'email' =>
                'required|email|unique:users',


            'password' =>
                'required|min:6|confirmed',


            'alamat' =>
                'required',


            'no_hp' =>
                'required|unique:users',


            'foto' =>
                'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);





        $foto = null;





        if (
            $request->hasFile(
                'foto'
            )
        ) {

            $file =

                $request->file(
                    'foto'
                );



            $fileName =

                time()

                . '_'

                . $file
                    ->getClientOriginalName();




            $file->move(

                public_path(
                    'uploads/users'
                ),

                $fileName
            );




            $foto =

                'uploads/users/'

                . $fileName;
        }







        User::create([

            'name' =>
                $request->name,


            'email' =>
                $request->email,


            'password' =>
                bcrypt(
                    $request->password
                ),


            'alamat' =>
                $request->alamat,


            'no_hp' =>
                $request->no_hp,


            'foto' =>
                $foto,


            'role' =>
                'kurir',


            'status' =>
                'aktif',


            'bank_sampah_id' =>

                Auth::user()
                    ->bank_sampah_id,
        ]);







        return redirect(
            '/admin/kurir'
        );
    }








    // ================= FORM EDIT =================
    public function edit($id)
    {

        $kurir =

            User::findOrFail(
                $id
            );



        return view(

            'admin.kurir.edit',

            compact(
                'kurir'
            )
        );
    }








    // ================= UPDATE =================
    public function update(
        Request $request,
        $id
    ) {

        $kurir =

            User::findOrFail(
                $id
            );





        $request->validate([

            'name' =>
                'required',


            'email' =>

                'required|email|unique:users,email,'

                . $kurir->id,


            'alamat' =>
                'required',


            'no_hp' =>
                'required',


            'foto' =>
                'nullable|image',
        ]);






        $foto =

            $kurir->foto;






        if (
            $request->hasFile(
                'foto'
            )
        ) {

            $file =

                $request->file(
                    'foto'
                );



            $fileName =

                time()

                . '_'

                . $file
                    ->getClientOriginalName();




            $file->move(

                public_path(
                    'uploads/users'
                ),

                $fileName
            );




            $foto =

                'uploads/users/'

                . $fileName;
        }







        $kurir->update([

            'name' =>
                $request->name,


            'email' =>
                $request->email,


            'alamat' =>
                $request->alamat,


            'no_hp' =>
                $request->no_hp,


            'foto' =>
                $foto,
        ]);







        return redirect(
            '/admin/kurir'
        );
    }








    // ================= DELETE =================
    public function destroy($id)
    {

        $kurir =

            User::findOrFail(
                $id
            );



        $kurir->delete();




        return redirect(
            '/admin/kurir'
        );
    }


}