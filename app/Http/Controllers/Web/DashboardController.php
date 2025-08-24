<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show main dashboard
     */
    public function index(): View
    {
        return view('dashboard.index');
    }

    /**
     * Show admin dashboard
     */
    public function admin(): View
    {
        return view('dashboard.admin');
    }

    /**
     * Show employee dashboard
     */
    public function employee(): View
    {
        return view('dashboard.employee');
    }
}

