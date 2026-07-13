<?php

namespace App\Http\Controllers;

use App\Support\Dashboard\DashboardViewModel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardViewModel $dashboard): View
    {
        return view('dashboard', $dashboard->for($request->user()));
    }
}
