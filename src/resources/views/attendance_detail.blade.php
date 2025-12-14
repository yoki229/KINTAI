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
                            <span></span>
                        </td>
                        <input type="hidden" name="name" value="">
                    </tr>

                    {{-- 日付 --}}
                    <tr class="attendance-detail-form__row">
                        <th class="attendance-detail-form__label">日付</th>
                        <td class="attendance-detail-form__data">

                        </td>
                        <input type="hidden" name="date" value="">
                    </tr>

                    {{-- 出勤・退勤 --}}
                    <tr class="attendance-detail-form__row">
                        <th class="attendance-detail-form__label">出勤・退勤</th>
                        <td class="attendance-detail-form__data">
                            <div class="form__name-inputs">
                                <input class="form-input__clock_in" type="text" name="clock_in"
                                    value="{{ old('clock_in') }}">
                                <input class="form-input__clock_out" type="text" name="clock_out"
                                    value="{{ old('clock_out') }}">
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


                        </td>
                    </tr>

                    {{-- 休憩２ --}}
                    <tr class="attendance-detail-form__row">
                        <th class="attendance-detail-form__label">休憩２</th>
                        <td class="attendance-detail-form__data">


                        </td>
                    </tr>

                    {{-- 備考 --}}
                    <tr class="attendance-detail-form__row">
                        <th class="attendance-detail-form__label">備考</th>
                        <td class="attendance-detail-form__data">

                        </td>
                    </tr>
                </table>
                <div class="attendance-detail-form__btn-inner">
                    <input class="request-button" type="submit" value="修正">
                </div>
            </form>
        </div>



        <div class="attendance-list__list">
            <table class="attendance-list">
                <thead>
                    <tr>
                        <th class="list-header">日付</th>
                        <th class="list-header">出勤</th>
                        <th class="list-header">退勤</th>
                        <th class="list-header">休憩</th>
                        <th class="list-header">合計</th>
                        <th class="list-header">詳細</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($attendances as $attendance)
                        <tr>
                            <td class="list-data">{{ $attendance->work_date->translatedFormat('m/d(D)') }}</td>
                            <td class="list-data">{{ $attendance->clock_in_formatted }}</td>
                            <td class="list-data">{{ $attendance->clock_out_formatted }}</td>
                            <td class="list-data">{{ $attendance->break_time_formatted }}</td>
                            <td class="list-data">{{ $attendance->work_time_formatted }}</td>
                            <td class="list-data">
                                <a class="list-data__detail" href="/attendance/detail/{{ $attendance->id }}">詳細</a>
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


<div class="attendance-detail-form">
    <h2 class="attendance-detail-form__heading content__heading">Confirm</h2>
    <div class="attendance-detail-form__inner">
        <form action="/thanks" method="post">
        @csrf
        <table class="attendance-detail-form__table">
            <tr class="attendance-detail-form__row">
            <th class="attendance-detail-form__label">お名前</th>
            <td class="attendance-detail-form__data">{{ $contacts['first_name'] }}&nbsp;{{ $contacts['last_name'] }}</td>
            <input type="hidden" name="first_name" value="{{ $contacts['first_name'] }}">
            <input type="hidden" name="last_name" value="{{ $contacts['last_name'] }}">
            </tr>
            <tr class="attendance-detail-form__row">
            <th class="attendance-detail-form__label">性別</th>
            <td class="attendance-detail-form__data">
                @if($contacts['gender'] == 1)
                男性
                @elseif($contacts['gender'] == 2)
                女性
                @else
                その他
                @endif
            </td>
            <input type="hidden" name="gender" value="{{ $contacts['gender'] }}">
            </tr>
            <tr class="attendance-detail-form__row">
            <th class="attendance-detail-form__label">メールアドレス</th>
            <td class="attendance-detail-form__data">{{ $contacts['email'] }}</td>
            <input type="hidden" name="email" value="{{ $contacts['email'] }}">
            </tr>
            <tr class="attendance-detail-form__row">
            <th class="attendance-detail-form__label">電話番号</th>
            <td class="attendance-detail-form__data">{{ $contacts['tel_1'] }}{{ $contacts['tel_2'] }}{{ $contacts['tel_3'] }}</td>
            <input type="hidden" name="tel_1" value="{{ $contacts['tel_1'] }}">
            <input type="hidden" name="tel_2" value="{{ $contacts['tel_2'] }}">
            <input type="hidden" name="tel_3" value="{{ $contacts['tel_3'] }}">
            </tr>
            <tr class="attendance-detail-form__row">
            <th class="attendance-detail-form__label">住所</th>
            <td class="attendance-detail-form__data">{{ $contacts['address'] }}</td>
            <input type="hidden" name="address" value="{{ $contacts['address'] }}">
            </tr>
            <tr class="attendance-detail-form__row">
            <th class="attendance-detail-form__label">建物名</th>
            <td class="attendance-detail-form__data">{{ $contacts['building'] }}</td>
            <input type="hidden" name="building" value="{{ $contacts['building'] }}">
            </tr>
            <tr class="attendance-detail-form__row">
            <th class="attendance-detail-form__label">お問い合わせの種類</th>
            <td class="attendance-detail-form__data">{{ $category->content }}</td>
            <input type="hidden" name="category_id" value="{{ $contacts['category_id'] }}">
            </tr>
            <tr class="attendance-detail-form__row">
            <th class="attendance-detail-form__label">お問い合わせ内容</th>
            <td class="attendance-detail-form__data">{{ $contacts['detail'] }}</td>
            <input type="hidden" name="detail" value="{{ $contacts['detail'] }}">
            </tr>
        </table>
        <div class="attendance-detail-form__btn-inner">
            <input class="attendance-detail-form__send-btn btn" type="submit" value="送信" name="send">
            <input class="attendance-detail-form__back-btn" type="submit" value="修正" name="back">
        </div>
        </form>
    </div>
</div>