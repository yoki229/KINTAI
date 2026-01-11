@extends('layouts.app')

<!-- タイトル -->
@section('title','勤怠登録')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

<!-- header読み込み -->
@section('header')
    @include('layouts.header')
@endsection

<!-- 本体 -->
@section('content')

    <div class="attendance__inner">
        <p class="status-label">
            {{ Auth::user()->attendance_status }}
        </p>

        <div class="clock">
            <div class="clock-date">
                {{ $now->format('Y年n月j日') }}({{ $now->translatedFormat('D') }})
            </div>
            <div class="clock-time">
                {{ $now->format('H:i') }}
            </div>
        </div>

        <div class="attendance-button__item">
            @if (!$attendance || $attendance->status === 'off_work')
                {{-- 出勤 --}}
                <form method="post" action="/attendance/clock-in">
                    @csrf
                    <button class="attendance-in-out-button">出勤</button>
                </form>

            @elseif ($attendance->status === 'working')
                {{-- 休憩・退勤 --}}
                <div class="status-working__item">
                    <form method="post" action="/attendance/clock-out">
                        @csrf
                        <button class="attendance-in-out-button">退勤</button>
                    </form>

                    <form method="post" action="/attendance/break-in">
                        @csrf
                        <button class="break-in-out-button">休憩入</button>
                    </form>
                </div>

            @elseif ($attendance->status === 'on_break')
                {{-- 休憩戻 --}}
                <form method="post" action="/attendance/break-out">
                    @csrf
                    <button class="break-in-out-button">休憩戻</button>
                </form>
            </div>

        @elseif ($attendance->status === 'finished')
            <p class="finished-message">お疲れ様でした。</p>
        @endif
    </div>

@endsection