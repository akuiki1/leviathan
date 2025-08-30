<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth');
    }

    public function login(Request $request)
    {
        // Validasi input (bisa pilih email atau NIP)
        $request->validate([
            'nip' => 'nullable|string',
            'email' => 'nullable|string|email',
            'password' => 'required|string',
            'remember' => 'nullable|boolean',
        ]);

        // Tentukan credentials
        if ($request->filled('email')) {
            $credentials = $request->only('email', 'password');
        } elseif ($request->filled('nip')) {
            $credentials = $request->only('nip', 'password');
        } else {
            return back()->withErrors(['login' => 'Email atau NIP harus diisi.']);
        }

        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user->role === 'admin') {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended(route('staff.dashboard.index'));
        }

        return back()->withErrors([
            'login' => 'Email/NIP atau password salah.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.form');
    }
}
