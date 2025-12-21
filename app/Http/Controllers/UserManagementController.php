<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Cabang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $user = User::when($search, function ($query, $search) {
            $query->where('nama', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('role', 'like', "%{$search}%");
        })->get();

        return view('user_management.index', compact('user', 'search'));
    }

    public function form($id = null)
    {
        $user = null;
        $detail = null;

        if ($id) {
            $user = User::with(['supplier', 'cabang'])->findOrFail($id);

            if ($user->role === 'Supplier') {
                $detail = $user->supplier;
            }

            if ($user->role === 'Cabang') {
                $detail = $user->cabang;
            }
        }

        return view('user_management.form', compact('user', 'detail'));
    }

    public function save(Request $request, $id = null)
    {
        $rules = [
            'username' => 'required|unique:users,username,' . $id . ',users_id',
            'email'    => 'required|email|unique:users,email,' . $id . ',users_id',
            'role'     => 'required|in:Manajer,Gudang,Cabang,Supplier,Kurir',
            'nama'     => 'required',
        ];

        // password wajib hanya saat create
        if (!$id) {
            $rules['password'] = 'required|min:6';
        }

        // validasi khusus role
        if (in_array($request->role, ['Supplier', 'Cabang'])) {
            $rules['alamat'] = 'required';
            $rules['kontak'] = 'required';
        }

        if ($request->role === 'Cabang') {
            $rules['nama_cabang'] = 'required';
        }

        $request->validate($rules);

        DB::transaction(function () use ($request, $id) {

            $user = User::updateOrCreate(
                ['users_id' => $id],
                [
                    'username'  => $request->username,
                    'email'     => $request->email,
                    'nama'      => $request->nama,
                    'role'      => $request->role,
                    'is_active' => 1,
                    ...(!$id ? ['password' => Hash::make($request->password)] : []),
                ]
            );

            /**
             * HAPUS RELASI LAMA (jika role berubah)
             */
            Supplier::where('users_id', $user->users_id)->delete();
            Cabang::where('users_id', $user->users_id)->delete();

            /**
             * SIMPAN RELASI SESUAI ROLE
             */
            if ($request->role === 'Supplier') {
                Supplier::create([
                    'users_id'        => $user->users_id,
                    'nama_supplier'   => $request->nama,
                    'alamat'          => $request->alamat,
                    'kontak'          => $request->kontak,
                    'status_supplier' => 'aktif'
                ]);
            }

            if ($request->role === 'Cabang') {
                Cabang::create([
                    'users_id'    => $user->users_id,
                    'nama_cabang' => $request->nama_cabang,
                    'alamat'      => $request->alamat,
                    'kontak'      => $request->kontak
                ]);
            }
        });

        return redirect()
            ->route('usermanagement')
            ->with('success',   $id
                ? 'User berhasil diperbarui'
                : 'User berhasil ditambahkan');
    }
}
