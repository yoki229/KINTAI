<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    public function adminLoginForm()
    {
        return view('auth/admin_login');
    }

    public function adminLoginPost(LoginRequest $request)
    {

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'admin',
        ];

        // role=admin のユーザーだけログインさせる
        if (Auth::guard('admin')->attempt($credentials, $request->filled('remember'))) {
            return redirect('/admin/attendance/list');
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }

    public function adminLogout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}

