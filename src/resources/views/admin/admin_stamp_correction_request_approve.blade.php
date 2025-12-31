@extends('layouts.app')

<!-- タイトル -->
@section('title','管理者用_修正申請承認')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/stamp_correction_request_approve.css') }}">
@endsection

<!-- header読み込み -->
@section('header')
    @include('layouts.header')
@endsection

<!-- 本体 -->
@section('content')

{{-- 承認完了メッセージ --}}
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
            <form action="/stamp_correction_request/approve/{{ $correction->id }}" method="post" novalidate>
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
                                <span class="plain-text">
                                    {{ $changes['clock_in'] ?? $attendance->clock_in_formatted }}
                                </span>
                                <span class="plain-text">
                                    ～
                                </span>
                                <span class="plain-text">
                                    {{ $changes['clock_out'] ?? $attendance->clock_out_formatted }}
                                </span>
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
                                <span class="plain-text">
                                {{ $changes['breaks'][$index]['start'] ?? $break->break_start_formatted ?? '' }}
                                </span>
                                <span class="plain-text">～</span>
                                <span class="plain-text">
                                {{ $changes['breaks'][$index]['end'] ?? $break->break_end_formatted ?? '' }}
                                </span>
                            </div>
                        </td>
                        <td class="space"></td>
                    </tr>
                    @endforeach

                    {{-- 備考 --}}
                    <tr class="attendance-detail-form__row">
                        <th class="attendance-detail-form__label">備考</th>
                        <td class="attendance-detail-form__data">
                            <div class="plain-text--note">
                                {{ $changes['note'] ?? $attendance->note ?? '' }}
                            </div>
                        </td>
                        <td class="space"></td>
                    </tr>
                </table>
                <div class="attendance-detail-form__btn-inner">
                    @if ($correction->status === 'pending')
                        <button class="pending-button" type="submit">承認</button>
                    @else
                        <p class="approved-message">承認済みです。</p>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection