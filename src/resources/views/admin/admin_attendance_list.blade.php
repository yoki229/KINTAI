@extends('layouts.app')

<!-- タイトル -->
@section('title','管理者用_勤怠一覧')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/admin_attendance_list.css') }}">
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
                <h1 class="title">{{ $currentDay }}の勤怠</h1>
            </div>

            {{-- 月日メニュー --}}
            <div class="attendance-list__date-menu">
                <div class="date-menu">
                    <a class="date-menu__day-link" href="{{ route('attendance.day', ['day' => $prevDay]) }}">
                        <i class="fa-sharp fa-solid fa-arrow-left"></i> 前日
                    </a>

                    <div class="date-menu__center">
                        <div class="day-picker">
                            <form method="get" action="{{ route('attendance.day') }}">
                                <input class="day-picker__icon" type="date" name="day"
                                    value="{{ $currentDayInput }}"
                                    onchange="this.form.submit()">
                            </form>
                        </div>
                        <span class="day">
                            {{ $currentDay }}
                        </span>
                    </div>

                    <a class="date-menu__day-link" href="{{ route('attendance.day', ['day' => $nextDay]) }}">
                        翌月 <i class="fa-sharp fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            {{-- 勤怠一覧 --}}
            <div class="attendance-list__list">
                <table class="attendance-list">
                    <thead>
                        <tr>
                            <th class="list-header">名前</th>
                            <th class="list-header">出勤</th>
                            <th class="list-header">退勤</th>
                            <th class="list-header">休憩</th>
                            <th class="list-header">合計</th>
                            <th class="list-header">詳細</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($attendances as $attendance)
                            <tr>
                                <td class="list-data">
                                    {{ $attendance->name }}
                                </td>
                                <td class="list-data">
                                    {{ $attendance->clock_in_formatted }}
                                </td>
                                <td class="list-data">
                                    {{ $attendance->clock_out_formatted }}
                                </td>
                                <td class="list-data">
                                    {{ $attendance->break_time_formatted }}
                                </td>
                                <td class="list-data">
                                {{ $attendance->work_time_formatted }}
                                </td>
                                <td class="list-data">
                                    <a class="list-data__detail" href="/admin/attendance/{{ $attendance->id }}">詳細</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection