<?php

namespace App\Http\Controllers\Manajer;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Cabang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function create()
    {
        return view('manajer.user.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:Supplier,Cabang,Kurir',
            'nama' => 'required',
            'email' => 'required|email|unique:users'
        ]);

        DB::transaction(function () use ($request) {

            $user = User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'nama' => $request->nama,
                'email' => $request->email,
                'is_active' => 1
            ]);

            if ($request->role === 'Supplier') {
                Supplier::create([
                    'users_id' => $user->users_id,
                    'nama_supplier' => $request->nama,
                    'alamat' => $request->alamat,
                    'kontak' => $request->kontak,
                    'status_supplier' => 'aktif'
                ]);
            }

            if ($request->role === 'Cabang') {
                Cabang::create([
                    'users_id' => $user->users_id,
                    'nama_cabang' => $request->nama,
                    'alamat' => $request->alamat,
                    'kontak' => $request->kontak
                ]);
            }
        });

        return redirect('/manajer')->with('success', 'User berhasil ditambahkan');
    }
}
