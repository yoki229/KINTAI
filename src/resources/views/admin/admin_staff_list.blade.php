@extends('layouts.app')

<!-- タイトル -->
@section('title','管理者用_スタッフ一覧')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('css//admin/admin_staff_list.css') }}">
@endsection

<!-- header読み込み -->
@section('header')
    @include('layouts.header')
@endsection

<!-- 本体 -->
@section('content')
    <div class="staff-list__inner">
        <div class="staff-list-content__inner">
            {{-- タイトル --}}
            <div class="staff-list__title">
                <h1 class="title">スタッフ一覧</h1>
            </div>

            {{-- スタッフ一覧 --}}
            <div class="staff-list__list">
                <table class="staff-list">
                    <thead>
                        <tr>
                            <th class="list-header--space"></th>
                            <th class="list-header">名前</th>
                            <th class="list-header">メールアドレス</th>
                            <th class="list-header">月次勤怠</th>
                            <th class="list-header--space"></th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($staffs as $staff)
                            <tr>
                                <td class="list-data--space"></td>
                                <td class="list-data">
                                    {{ $staff->name }}
                                </td>
                                <td class="list-data">
                                    {{ $staff->email }}
                                </td>
                                <td class="list-data">
                                    <a class="list-data__detail" href="/admin/attendance/staff/{{ $staff->id }}">詳細</a>
                                </td>
                                <td class="list-data--space"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection