<?php

namespace App\Providers;

use App\Models\User;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\LogoutResponse;

class FortifyServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(function () {
            return view('auth.register');
        });

        Fortify::loginView(function () {
            return view('auth.login');
        });

        Fortify::redirects('login', '/attendance');

        RateLimiter::for('login', function (Request $request) {
            if ($request->is('admin/login')) {
                return Limit::none();
            }

            $email = (string) $request->email;
            return Limit::perMinute(10)->by($email . $request->ip());
        });

        // 一般ユーザーのログインなら通す
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if ($request->is('login')) {

                // ユーザーが存在しないか'user'でなければログイン失敗
                if (! $user || $user->role !== 'user') {
                    return null;
                }
            }

            // パスワードをチェックしてログインを通す
            if($user && Hash::check($request->password, $user->password)) {
                return $user;
                }

            return null;
        });

        app()->singleton(LogoutResponse::class, function () {
            return new class implements LogoutResponse {
                public function toResponse($request)
                {
                    // 管理者がログイン中か
                    if (Auth::check() && Auth::user()->role === 'admin') {
                        Auth::logout();
                        return redirect('/admin/login');
                    }

                    // 一般ユーザーは一般用ログインページへ
                    Auth::logout();
                    return redirect('/login');
                }
            };
        });
    }
}
