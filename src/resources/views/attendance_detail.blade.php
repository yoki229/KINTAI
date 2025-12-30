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
            <form action="/attendance/detail/{{ $attendance->id }}/correction" method="post" novalidate>
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
                                            value="{{ old('clock_in', $attendance->clock_in_formatted) }}">
                                    </div>
                                    <span class="clock-inputs__item">～</span>
                                    <div class="clock-inputs__item">
                                        <input class="form__clock-input" type="time" name="clock_out"
                                            value="{{ old('clock_out', $attendance->clock_out_formatted) }}">
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
                    @foreach($breaks as $index => $break)
                    <tr class="attendance-detail-form__row">
                        <th class="attendance-detail-form__label">
                            休憩{{ $index + 1 }}
                        </th>
                        <td class="attendance-detail-form__data">
                            <div class="form__clock-inputs">
                                @if($isPending)
                                    <span class="plain-text">
                                        {{ $changes['breaks'][$index]['start'] ?? $break->break_start_formatted ?? '' }}
                                    </span>
                                    <span class="plain-text">～</span>
                                    <span class="plain-text">
                                        {{ $changes['breaks'][$index]['end'] ?? $break->break_end_formatted ?? '' }}
                                    </span>
                                @else
                                    <div class="clock-inputs__item">
                                        <input
                                            class="form__clock-input"
                                            type="time"
                                            name="breaks[{{ $index }}][start]"
                                            @if(old("breaks.$index.start") !== null)
                                                value="{{ old("breaks.$index.start") }}"
                                            @elseif($break->break_start)
                                                value="{{ $break->break_start_formatted }}"
                                            @endif
                                        >
                                    </div>
                                    <span class="clock-inputs__item">～</span>
                                    <div class="clock-inputs__item">
                                        <input
                                            class="form__clock-input"
                                            type="time"
                                            name="breaks[{{ $index }}][end]"
                                            @if(old("breaks.$index.end") !== null)
                                                value="{{ old("breaks.$index.end") }}"
                                            @elseif($break->break_end)
                                                value="{{ $break->break_end_formatted }}"
                                            @endif
                                        >
                                </div>
                                @endif
                            </div>
                            <div class="form__error-message">
                                @error("breaks.$index.start")
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                                @error("breaks.$index.end")
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>
                        </td>
                        <td class="space"></td>
                    </tr>
                    @endforeach

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