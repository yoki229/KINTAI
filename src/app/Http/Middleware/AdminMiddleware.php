<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('admin')->check()) {
            return $next($request);
        }

        // 一般ユーザーなら弾く
        return redirect('/admin/login')->withErrors([
            'email' => '管理者のみログインできます。',
        ]);
    }
}
