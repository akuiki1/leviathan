<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Tim;
use App\Models\Honorarium;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Hitung honorarium yg sudah dia ambil
        $honorCount = Honorarium::where('user_id', $user->id)->count();

        // Ambil semua tim yg dia ikuti
        $tims = $user->tims ?? [];

        return view('admin.dashboard', compact('user', 'honorCount', 'tims'));
    }
}
