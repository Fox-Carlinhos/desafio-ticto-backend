<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function login(Request $request): RedirectResponse
    {
        return redirect()->route('login');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request): RedirectResponse
    {
        return redirect()->route('login');
    }

    /**
     * Show user profile
     */
    public function profile(): View
    {
        return view('profile.index');
    }

    /**
     * Show settings page
     */
    public function settings(): View
    {
        return view('settings.index');
    }
}

