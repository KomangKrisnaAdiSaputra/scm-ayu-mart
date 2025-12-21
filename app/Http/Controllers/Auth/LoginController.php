<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        if (Auth::attempt([
            'username' => $request->username,
            'password' => $request->password,
            'is_active' => 1
        ])) {

            $request->session()->regenerate();

            return redirect()->route('dashboard');
            // return match (auth()->user()->role) {
            //     'Gudang'   => redirect('/gudang'),
            //     'Manajer'  => redirect('/manajer'),
            //     'Supplier' => redirect('/supplier'),
            //     'Cabang'   => redirect('/cabang'),
            //     'Kurir'    => redirect('/kurir'),
            //     default    => redirect('/login')
            // };
        }

        return back()->withErrors([
            'username' => 'Username atau password salah'
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
