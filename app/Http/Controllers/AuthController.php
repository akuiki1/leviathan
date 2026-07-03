<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth');
    }

    public function login(Request $request)
    {
        $request->validate([
            'nip' => 'required|string',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        if (!Auth::attempt(['nip' => $request->nip, 'password' => $request->password], $remember)) {
            return back()->withErrors([
                'login' => 'NIP atau password salah.',
            ])->withInput($request->only('nip'));
        }

        $request->session()->regenerate();

        return Auth::user()->role === 'admin'
            ? redirect()->intended(route('admin.dashboard'))
            : redirect()->intended(route('staff.dashboard.index'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.form');
    }
}
