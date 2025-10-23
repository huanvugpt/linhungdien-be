<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * Handle admin login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('admin')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Redirect to admin dashboard
            return redirect()->route('admin.dashboard')->with('success', 'Đăng nhập thành công!');
        }

        // Login failed - redirect back with error
        \Log::info('Login failed for: ' . $request->email);
        \Log::info('Has errors: ' . ($request->session()->has('errors') ? 'yes' : 'no'));
        
        return back()
            ->withErrors(['email' => 'Thông tin đăng nhập không chính xác.'])
            ->withInput($request->only('email'));
    }

    /**
     * Handle admin logout.
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('admin.login');
    }
}
