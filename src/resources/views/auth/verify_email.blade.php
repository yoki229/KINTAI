@extends('layouts.app')

<!-- タイトル -->
@section('title','メール認証')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify.css')  }}">
@endsection

<!-- 本体 -->
@section('content')

@include('layouts.header')

{{-- 未認証時のメッセージ --}}
@if(session('message'))
    <p class="message">{{session('message')}}</p>
@endif

<div class="mail">
    <div class="text">
        <p>登録していただいたメールアドレスに認証メールを送付しました。</p>
        <p>メール認証を完了してください。</p>
    </div>

    <form method="GET" action="/email/check">
        <button class="send" type="submit">認証はこちらから</button>
    </form>


    <form method="POST" action="/email/resend">
        @csrf
        <button class="resend" type="submit">確認メールを再送する</button>
    </form>

</div>
@endsection