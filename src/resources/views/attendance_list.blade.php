@extends('layouts.app')

<!-- タイトル -->
@section('title','勤怠一覧')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endsection

<!-- header読み込み -->
@section('header')
    @include('layouts.header')
@endsection

<!-- 本体 -->
@section('content')
    <div class="attendance-list__inner">
        <div class="attendance-list-content__inner">
            {{-- タイトル --}}
            <div class="attendance-list__title">
                <h1 class="title">勤怠一覧</h1>
            </div>

            {{-- 月日メニュー --}}
            <div class="attendance-list__date-menu">
                <div class="date-menu">
                    <a href="{{ route('attendance.month', ['month' => $prevMonth]) }}">
                        ← 前月
                    </a>

                    <div class="date-menu__center">
                        <div class="month-picker">
                            <form method="get" action="{{ route('attendance.month') }}">
                                <input class="month-picker__icon" type="month" name="month"
                                    value="{{ $currentMonthInput }}"
                                    onchange="this.form.submit()">
                            </form>
                        </div>
                        <span class="month">
                            {{ $currentMonth }}
                        </span>
                    </div>

                    <a href="{{ route('attendance.month', ['month' => $nextMonth]) }}">
                        翌月 →
                    </a>
                </div>
            </div>

            {{-- 勤怠一覧 --}}
            <div class="attendance-list__list">
                <table class="attendance-list">
                    <thead>
                        <tr>
                            <th>日付</th>
                            <th>出勤</th>
                            <th>退勤</th>
                            <th>休憩</th>
                            <th>合計</th>
                            <th>詳細</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($attendances as $attendance)
                            <tr>
                                <td>{{ $attendance->work_date->format('m/d') }}</td>
                                <td>{{ $attendance->clock_in_formatted }}</td>
                                <td>{{ $attendance->clock_out_formatted }}</td>
                                <td>{{ $attendance->break_time_formatted }}</td>
                                <td>{{ $attendance->work_time_formatted }}</td>
                                <td>
                                    <a href="/attendance/detail/{{ $attendance->id }}">詳細</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">勤怠データがありません</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection