@extends('layouts.app')

<!-- タイトル -->
@section('title','管理者ログイン')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('css/layouts/header.css') }}">
<link rel="stylesheet" href="{{ asset('css/auth/admin_login.css')}}">
@endsection

<!-- header読み込み -->
@section('header')
    @include('layouts.header')
@endsection

<!-- 本体 -->
@section('content')

@endsection