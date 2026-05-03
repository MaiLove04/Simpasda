<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Approve user oleh admin DLH
     */
    public function approve(Request $request)
    {
        // validasi input
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        // ambil user
        $user = User::find($request->user_id);

        // update status
        $user->status = 'approved';
        $user->save();

        return response()->json([
            'message' => 'User berhasil di-approve',
            'data' => $user
        ]);
    }

    /**
     * List semua user (optional, buat admin)
     */
    public function index()
    {
        $users = User::all();

        return response()->json($users);
    }
}