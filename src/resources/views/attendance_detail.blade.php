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

{{-- 不正時アクセス時のメッセージ --}}
@if(session('error'))
    <p class="error">{{session('error')}}</p>
@endif

{{-- 申請完了メッセージ --}}
@if(session('success'))
    <p class="success">{{session('success')}}</p>
@endif

<div class="attendance-detail__inner">

    <div class="attendance-detail-content__inner">
        {{-- タイトル --}}
        <div class="attendance-detail__title">
            <h1 class="title">勤怠詳細</h1>
        </div>

        {{-- 申請フォーム --}}
        <div class="attendance-detail-form__inner">
            <form action="/attendance/detail/{{ $attendance->id }}/Correction" method="post" novalidate>
            @csrf
                <table class="attendance-detail-form__table">
                    <colgroup>
                    <col class="col-label">
                    <col class="col-data">
                    <col class="col-space">
                    </colgroup>

                    {{-- 名前 --}}
                    <tr class="attendance-detail-form__row">
                        <th class="attendance-detail-form__label">名前</th>
                        <td class="attendance-detail-form__data">
                            <span class="name">
                                {{ $attendance->user->name }}</span>
                        </td>
                        <td class="space"></td>
                    </tr>

                    {{-- 日付 --}}
                    <tr class="attendance-detail-form__row">
                        <th class="attendance-detail-form__label">日付</th>
                        <td class="attendance-detail-form__data">
                            <div class="form__date-inputs">
                                <span class="date">
                                    {{ $attendance->work_date->format('Y年') }}</span>
                                <span class="date">
                                    {{ $attendance->work_date->format('n月j日') }}</span>
                            </div>
                        </td>
                        <td class="space"></td>
                    </tr>

                    {{-- 出勤・退勤 --}}
                    <tr class="attendance-detail-form__row">
                        <th class="attendance-detail-form__label">出勤・退勤</th>
                        <td class="attendance-detail-form__data">
                            <div class="form__clock-inputs">
                                @if($isPending)
                                    <span class="plain-text">
                                        {{ $changes['clock_in'] ?? '' }}
                                    </span>
                                    <span class="plain-text">
                                        ～
                                    </span>
                                    <span class="plain-text">
                                        {{ $changes['clock_out'] ?? '' }}
                                    </span>
                                @else
                                    <div class="clock-inputs__item">
                                        <input class="form__clock-input" type="time" name="clock_in"
                                            value="{{ old('clock_in', optional($attendance->clock_in)->format('H:i')) }}">
                                    </div>
                                    <span class="clock-inputs__item">～</span>
                                    <div class="clock-inputs__item">
                                        <input class="form__clock-input" type="time" name="clock_out"
                                            value="{{ old('clock_out', optional($attendance->clock_out)->format('H:i')) }}">
                                    </div>
                                @endif
                            </div>
                            <div class="form__error-message">
                                @error('clock_in')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                                @error('clock_out')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>
                        </td>
                        <td class="space"></td>
                    </tr>

                    {{-- 休憩 --}}
                    <tr class="attendance-detail-form__row">
                        <th class="attendance-detail-form__label">休憩</th>
                        <td class="attendance-detail-form__data">
                            <div class="form__clock-inputs">
                                @if($isPending)
                                    <span class="plain-text">
                                        {{ $changes['break1_start'] ?? '' }}
                                    </span>
                                    <span class="plain-text">
                                        ～
                                    </span>
                                    <span class="plain-text">
                                        {{ $changes['break1_end'] ?? '' }}
                                    </span>
                                @else
                                    <div class="clock-inputs__item">
                                        <input class="form__clock-input" type="time" name="break1_start"
                                            value="{{ old('break1_start', optional($breaks[0]?->break_start)->format('H:i')) }}">
                                    </div>
                                    <span class="clock-inputs__item">～</span>
                                    <div class="clock-inputs__item">
                                        <input class="form__clock-input" type="time" name="break1_end"
                                            value="{{ old('break1_end', optional($breaks[0]?->break_end)->format('H:i')) }}">
                                    </div>
                                @endif
                            </div>
                            <div class="form__error-message">
                                @error('break1_start')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                                @error('break1_end')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>
                        </td>
                        <td class="space"></td>
                    </tr>

                    {{-- 休憩２ --}}
                    <tr class="attendance-detail-form__row">
                        <th class="attendance-detail-form__label">休憩２</th>
                        <td class="attendance-detail-form__data">
                            <div class="form__clock-inputs">
                                @if($isPending)
                                    <span class="plain-text">
                                        {{ $changes['break2_start'] ?? '' }}
                                    </span>
                                    <span class="plain-text">
                                        ～
                                    </span>
                                    <span class="plain-text">
                                        {{ $changes['break2_end'] ?? '' }}
                                    </span>
                                @else
                                    <div class="clock-inputs__item">
                                        <input class="form__clock-input" type="time" name="break2_start"
                                            value="{{ old('break2_start', optional($breaks[1]?->break_start)->format('H:i')) }}">
                                    </div>
                                    <span class="clock-inputs__item">～</span>
                                    <div class="clock-inputs__item">
                                        <input class="form__clock-input" type="time" name="break2_end"
                                            value="{{ old('break2_end', optional($breaks[1]?->break_end)->format('H:i')) }}">
                                    </div>
                                @endif
                            </div>
                            <div class="form__error-message">
                                @error('break2_start')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                                @error('break2_end')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>
                        </td>
                        <td class="space"></td>
                    </tr>

                    {{-- 備考 --}}
                    <tr class="attendance-detail-form__row">
                        <th class="attendance-detail-form__label">備考</th>
                        <td class="attendance-detail-form__data">
                            @if($isPending)
                                <div class="plain-text--note">
                                    {{ $changes['note'] ?? '' }}
                                </div>
                            @else
                                <textarea class="note" name="note" rows="3">{{ old('note', $attendance->note ?? '') }}</textarea>
                            @endif
                            <div class="form__error-message">
                            @error('note')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                            </div>
                        </td>
                        <td class="space"></td>
                    </tr>
                </table>
                <div class="attendance-detail-form__btn-inner">
                    @if($isPending)
                        <p class="pending-message">・承認待ちのため修正はできません。</p>
                    @else
                        <button class="request-button" type="submit">修正</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection