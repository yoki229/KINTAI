<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class MailController extends Controller
{
    // メール認証を促すページ
    public function email(){
        return view('auth.verify_email');
    }

    // メール認証のリンクをクリックしたときの処理
    public function verify(EmailVerificationRequest $request){
        $request->fulfill(); // 認証完了
        Auth::login($request->user());
        return redirect('/attendance');
    }

    // 認証はこちらからをクリックしたときの処理
    public function emailCheck(){
        $user = auth()->user();

        if ($user->hasVerifiedEmail()) {
            return redirect('/attendance');
        }
        return redirect('/email')
        ->with('message', 'メール認証を完了してください。');
    }

    // メール再送信処理
    public function resend(Request $request){
        $request->user()->sendEmailVerificationNotification();
        return back();
    }
}