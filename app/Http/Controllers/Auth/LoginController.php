<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            $request->session()->forget('url.intended');

            // Update last login
            auth()->user()->update(['last_login_at' => now()]);

            // Redirect based on role or department/email fallback
            $user = auth()->user();
            if ($user->hasRole('admin') || $user->department === 'Management' || str_contains($user->email, 'admin')) {
                \Spatie\Permission\Models\Role::findOrCreate('admin', 'web');
                if (!$user->hasRole('admin')) {
                    $user->assignRole('admin');
                }
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('employee.dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }
}
