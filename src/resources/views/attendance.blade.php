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
            {{ $attendance->status_label ?? '勤務外' }}
        </p>

        <div class="clock">
            <div class="clock-date" id="clock-date"></div>
            <div class="clock-time" id="clock-time"></div>
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

<script>
function updateClock() {
    const now = new Date();
    const week = ['日','月','火','水','木','金','土'];

    const dateText =
        now.getFullYear() + '年' +
        (now.getMonth() + 1) + '月' +
        now.getDate() + '日' +
        '(' + week[now.getDay()] + ')';

    const timeText =
        String(now.getHours()).padStart(2,'0') + ':' +
        String(now.getMinutes()).padStart(2,'0');

    document.getElementById('clock-date').innerText = dateText;
    document.getElementById('clock-time').innerText = timeText;
}
setInterval(updateClock, 1000);
updateClock();
</script>

@endsection