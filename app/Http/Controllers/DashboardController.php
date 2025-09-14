<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Tim;
class DashboardController extends Controller
{
    public function index()
    {
        $userCount = User::count();
        $timCount = Tim::count();
        $approvedTimCount = Tim::where('status', 'approved')->count();
        $recentUsers = User::latest()->take(5)->get();
        $recentPendingTims = Tim::where('status', 'pending')->latest()->take(5)->get();

        return view('admin.dashboard', compact('userCount', 'timCount', 'approvedTimCount', 'recentUsers', 'recentPendingTims'));
    }
}
