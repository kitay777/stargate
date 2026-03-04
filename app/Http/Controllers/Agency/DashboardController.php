<?php
// app/Http/Controllers/Agency/DashboardController.php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $agencyUser = Auth::guard('agency')->user();
        $agency = $agencyUser->agency()->with(['users.profile'])->first();

    

        return Inertia::render('Agency/AgencyDashboard', [
            'agencyUser' => $agencyUser,
            'agency' => $agency,
            'streamers' => $agency->users, // 所属ライバー一覧
        ]);
    }
}
