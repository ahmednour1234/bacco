<?php

namespace App\Http\Controllers\Enduser;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $stats = [
            'total_quotations'     => 0,
            'active_quotations'    => 0,
            'completed_quotations' => 0,
            'active_projects'      => 0,
            'completed_projects'   => 0,
            'accepted_quotations'  => 0,
        ];

        return view('enduser.dashboard', compact('stats'));
    }
}
