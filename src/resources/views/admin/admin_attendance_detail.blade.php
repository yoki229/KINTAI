@extends('layouts.app')

<!-- タイトル -->
@section('title','管理者用_勤怠詳細')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/admin_attendance_detail.css') }}">
@endsection

<!-- header読み込み -->
@section('header')
    @include('layouts.header')
@endsection

<!-- 本体 -->
@section('content')




<!--
管理画面での利用例
@foreach ($attendance->breaks as $break)
    {{ $break->break_start }} 〜 {{ $break->break_end }}
@endforeach
-->

@endsection