<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserSettingController extends Controller
{
    public function index()
    {
        return view('settings.user_login', [
            'user' => auth()->user()
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'nama'     => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username,' . $user->users_id . ',users_id',
            'email'    => 'required|email|unique:users,email,' . $user->users_id . ',users_id',
            'password' => 'nullable|min:6|confirmed',
        ]);

        $user->update([
            'nama'     => $request->nama,
            'username' => $request->username,
            'email'    => $request->email,
        ]);

        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return back()->with('success', 'Pengaturan berhasil diperbarui');
    }
}
