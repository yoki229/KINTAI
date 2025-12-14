@extends('layouts.app')

<!-- タイトル -->
@section('title','申請一覧')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request_list.css') }}">
@endsection

<!-- header読み込み -->
@section('header')
    @include('layouts.header')
@endsection

<!-- 本体 -->
@section('content')
<div class="correction_request__inner">
    <div class="correction_request-content__inner">
        {{-- タイトル --}}
        <div class="correction_request__title">
            <h1 class="title">申請一覧</h1>
        </div>
    </div>

</div>
@endsection