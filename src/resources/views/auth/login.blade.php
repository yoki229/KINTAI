@extends('layouts.app')

<!-- タイトル -->
@section('title','ログイン')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('css/layouts/header.css') }}">
<link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endsection

<!-- header読み込み -->
@section('header')
    @include('layouts.header')
@endsection

<!-- 本体 -->
@section('content')

<div class="login-form">
    <h1 class="login-form__heading content__heading">ログイン</h1>
    <div class="login-form__inner">
        <form class="login-form__form" action="/login" method="post" novalidate>
        @csrf

            {{-- メールアドレス --}}
            <div class="login-form__group">
                <label class="login-form__label" for="email">メールアドレス</label>
                <input class="login-form__input" type="text" name="email" id="email" value="{{ old('email') }}">
                @error('email')
                <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            {{-- パスワード --}}
            <div class="login-form__group">
                <label class="login-form__label" for="password">パスワード</label>
                <input class="login-form__input" type="password" name="password" id="password">
                @error('password')
                <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <input class="login-form__btn" type="submit" value="ログインする">
        </form>
        <a class="register__link" href="/register">会員登録はこちら</a>
    </div>
</div>
@endsection