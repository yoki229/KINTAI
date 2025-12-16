@extends('layouts.app')

<!-- タイトル -->
@section('title','勤怠詳細')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

<!-- header読み込み -->
@section('header')
    @include('layouts.header')
@endsection

<!-- 本体 -->
@section('content')
<div class="attendance-detail__inner">
    <div class="attendance-detail-content__inner">
        {{-- タイトル --}}
        <div class="attendance-detail__title">
            <h1 class="title">勤怠詳細</h1>
        </div>

        {{-- 申請フォーム --}}
        <div class="attendance-detail-form__inner">
            <form action="/attendance/detail/{{ $attendance->id }}/Correction" method="post">
            @csrf
                <table class="attendance-detail-form__table">
                    {{-- 名前 --}}
                    <tr class="attendance-detail-form__row">
                        <th class="attendance-detail-form__label">名前</th>
                        <td class="attendance-detail-form__data">
                            <span>{{ $attendance->user->name }}</span>
                        </td>
                    </tr>

                    {{-- 日付 --}}
                    <tr class="attendance-detail-form__row">
                        <th class="attendance-detail-form__label">日付</th>
                        <td class="attendance-detail-form__data">
                            <span>{{ $attendance->work_date->format('Y年') }}</span>
                            <span>{{ $attendance->work_date->format('m/d') }}</span>
                        </td>
                    </tr>

                    {{-- 出勤・退勤 --}}
                    <tr class="attendance-detail-form__row">
                        <th class="attendance-detail-form__label">出勤・退勤</th>
                        <td class="attendance-detail-form__data">
                            <div class="form__name-inputs">
                                <input class="form-input__clock_in" type="time" name="clock_in"
                                    value="{{ optional($attendance->clock_in)->format('H:i') }}">
                                <input class="form-input__clock_out" type="time" name="clock_out"
                                    value="{{ optional($attendance->clock_out)->format('H:i') }}">
                            </div>
                            <div class="form__name-error-message">
                                @if ($errors->has('clock_in'))
                                    <p class="name__error-message-clock_in">{{$errors->first('clock_in')}}</p>
                                @endif
                                @if ($errors->has('clock_out'))
                                    <p class="name__error-message-clock_in">{{$errors->first('clock_out')}}</p>
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- 休憩 --}}
                    <tr class="attendance-detail-form__row">
                        <th class="attendance-detail-form__label">休憩</th>
                        <td class="attendance-detail-form__data">
                            <input type="time" name="break1_start"
                                    value="{{ $breaks[0]?->break_start ?? null }}">
                            〜
                            <input type="time" name="break1_end"
                                    value="{{ $breaks[0]?->break_end ?? null }}">
                        </td>
                    </tr>

                    {{-- 休憩２ --}}
                    <tr class="attendance-detail-form__row">
                        <th class="attendance-detail-form__label">休憩２</th>
                        <td class="attendance-detail-form__data">
                            <input type="time" name="break2_start"
                                    value="{{ $breaks[1]?->break_start ?? null }}">
                            〜
                            <input type="time" name="break2_end"
                                    value="{{ $breaks[1]?->break_end ?? null }}">
                        </td>
                    </tr>

                    {{-- 備考 --}}
                    <tr class="attendance-detail-form__row">
                        <th class="attendance-detail-form__label">備考</th>
                        <td class="attendance-detail-form__data">
                            <textarea name="note" rows="3"></textarea>
                        </td>
                    </tr>
                </table>
                <div class="attendance-detail-form__btn-inner">
                    <button class="request-button" type="submit">修正</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection