<?php

namespace App\Domain\Auth\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if (!$user->isAdmin()) {
                Auth::logout();

                return back()->withErrors(['email' => 'Unauthorized']);
            }

            return redirect()->intended('/telescope');
        }

        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->route('admin.login');
    }
}
