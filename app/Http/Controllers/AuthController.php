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
        $request->validate([
            'nip' => 'required|string',
            'password' => 'required|string',
        ]);

        $latestBatch = User::max('batch');
        $remember = $request->has('remember'); // ambil nilai checkbox remember

        // Cari user dengan NIP yang sesuai di batch terbaru
        $user = User::where('nip', $request->nip)
            ->where('batch', $latestBatch)
            ->first();

        // Coba login dengan remember me
        if (!$user || !Auth::attempt([
            'nip' => $request->nip,
            'password' => $request->password,
            'batch' => $latestBatch
        ], $remember)) { // passing remember ke attempt()
            return back()->withErrors([
                'login' => 'NIP atau password salah, atau akun Anda bukan dari batch terbaru.'
            ])->withInput($request->only('nip'));
        }

        // Regenerate session untuk keamanan
        $request->session()->regenerate();

        // Set cookie remember me jika dicentang
        if ($remember) {
            // Cookie akan bertahan 30 hari
            cookie()->queue('remember_web', encrypt($user->id), 43200);
        }

        if ($user->role === 'admin') {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('staff.dashboard.index'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.form');
    }
}
