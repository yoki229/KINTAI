@section('css')
<link rel="stylesheet" href="{{ asset('/css/layouts/header.css')  }}">
@endsection

@section('header')
    <!-- ヘッダー -->
    <header class="header">
        <div class="header-nav__logo">
            <a class="header-nav__logo-link" href="/attendance">
                <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH" class="header__img">
            </a>
        </div>

        @if (!Request::is('login', 'register', 'email*')
        && Auth::check()
        && Auth::user()->hasVerifiedEmail())

        <ul class="header-nav__list">
            @if (Auth::user()->role === 'admin')
                <li>
                    <a href="/admin/attendance/list" class="list__admin-attendance-list">勤怠一覧</a>
                </li>
                <li>
                    <a href="/admin/staff/list" class="admin-staff-list">スタッフ一覧</a>
                </li>
                <li>
                    <a href="/stamp_correction_request/list" class="list__admin-stamp-correction-request">申請一覧</a>
                </li>
            @else
                <li>
                    <a href="/attendance" class="list__attendance">勤怠</a>
                </li>
                <li>
                    <a href="/stamp_correction_request/list" class="list__attendance-list">勤怠一覧</a>
                </li>
                <li>
                    <a href="/stamp_correction_request/list" class="list__stamp-correction-request">申請</a>
                </li>
            @endif
            <li>
                <form action="/logout" method="post">
                    @csrf
                        <button class="list__logout">ログアウト</button>
                </form>
            </li>
        </ul>
    @endif
    </header>
@endsection