<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Role-aware dashboard router — spec §2 navigation matrix. */
    public function index(Request $request): View
    {
        $user = $request->user();

        $dashboard = match (true) {
            $user->hasRole(Role::Farmer) => view('dashboards.farmer', ['user' => $user]),
            $user->hasRole(Role::Supplier) => view('dashboards.supplier', ['user' => $user]),
            $user->hasRole(Role::Transporter) => view('dashboards.transporter', ['user' => $user]),
            $user->hasRole(Role::JobSeeker) => view('dashboards.job-seeker', ['user' => $user]),
            default => view('dashboards.buyer', ['user' => $user]),
        };

        return $dashboard;
    }
}
