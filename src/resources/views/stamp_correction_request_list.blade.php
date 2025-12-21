@extends('layouts.app')

<!-- タイトル -->
@section('title','申請一覧')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request_list.css') }}">
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

<div class="correction_request__inner">
    <div class="correction_request-content__inner">
        {{-- タイトル --}}
        <div class="correction_request__title">
            <h1 class="title">申請一覧</h1>
        </div>
    </div>

    {{-- リスト切り替え --}}
    <div class="list-menu">
        <a href="/stamp_correction_request/list/?tab=pending"
        class="list-menu__pending {{ $tab === 'pending' ? 'active' : '' }}">
        承認待ち
        </a>

        <a href="/stamp_correction_request/list/?tab=approved"
        class="list-menu__approved {{ $tab === 'approved' ? 'active' : '' }}">
        承認済み
        </a>
    </div>

    <hr class="hr">

    {{-- 申請一覧 --}}
    <div class="attendance-list__list">
        <table class="attendance-list">
            <thead>
                <tr>
                    <th class="list-header">状態</th>
                    <th class="list-header">名前</th>
                    <th class="list-header">対象日時</th>
                    <th class="list-header">申請理由</th>
                    <th class="list-header">申請日時</th>
                    <th class="list-header">詳細</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($corrections as $correction)
                    <tr>
                        <td class="list-data">
                            {{ $correction->status === 'pending' ? '未承待ち' : '承認済み' }}
                        </td>
                        <td class="list-data">
                            {{ $correction->user->name }}
                        </td>
                        <td class="list-data">
                            {{ $correction->attendanceRecord->work_date->format('Y/m/d') }}
                        </td>
                        <td class="list-data">
                            {{ $correction->reason }}
                        </td>
                        <td class="list-data">
                            {{ $correction->created_at->format('Y/m/d') }}
                        </td>
                        <td class="list-data">
                            <a class="list-data__detail" href="/attendance/detail/{{ $correction->attendance_record_id }}">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection