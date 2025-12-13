@extends('layouts.app')

<!-- タイトル -->
@section('title','会員登録')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css')}}">
@endsection

<!-- header読み込み -->
@section('header')
    @include('layouts.header')
@endsection

<!-- 本体 -->
@section('content')

@include('layouts.header')

<div class="register-form">
    <h1 class="register-form__heading content__heading">会員登録</h1>
    <div class="register-form__inner">
        <form class="register-form__form" action="/register" method="post">
        @csrf

            {{-- 名前 --}}
            <div class="register-form__group">
                <label class="register-form__label" for="name">名前</label>
                <input class="register-form__input" type="text" name="name" id="name" value="{{ old('name') }}">
                @error('name')
                <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            {{-- メールアドレス --}}
            <div class="register-form__group">
                <label class="register-form__label" for="email">メールアドレス</label>
                <input class="register-form__input" type="email" name="email" id="email" value="{{ old('email') }}">
                @error('email')
                <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            {{-- パスワード --}}
            <div class="register-form__group">
                <label class="register-form__label" for="password">パスワード</label>
                <input class="register-form__input" type="password" name="password" id="password">
                @error('password')
                    @if (!str_contains($message, '一致'))
                        <p class="error-message">{{ $message }}</p>
                    @endif
                @enderror
            </div>

            {{-- パスワード確認 --}}
            <div class="register-form__group">
                <label class="register-form__label" for="password_confirmation">パスワード確認</label>
                <input class="register-form__input" type="password" name="password_confirmation" id="password_confirmation">
                @error('password')
                    @if (str_contains($message, '一致'))
                        <p class="error-message">{{ $message }}</p>
                    @endif
                @enderror
            </div>

            <input class="register-form__btn btn" type="submit" value="登録する">
        </form>
        <a class="login__link" href="/login">ログインはこちら</a>
    </div>
</div>

@endsection