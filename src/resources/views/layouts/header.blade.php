
@section('header')
    <header class="header">
        {{-- ヘッダーロゴ --}}
        <div class="header-nav__logo">
            @if (Auth::guard('admin')->check())
                {{-- 管理者ログイン中 --}}
                <a class="header-nav__logo-link" href="/admin/attendance/list">

            @elseif (Auth::guard('web')->check())
                {{-- 一般ユーザーログイン中 --}}
                <a class="header-nav__logo-link" href="/attendance">

            @else
                {{-- 未ログイン（リンクなし） --}}
                <span class="header-nav__logo-link">
            @endif

                <img src="{{ asset('images/CoachTech_White 1.png') }}" alt="COACHTECH" class="header__img">

            @if (Auth::guard('admin')->check() || Auth::guard('web')->check())
                </a>
            @else
                </span>
            @endif
        </div>

        {{-- ヘッダーメニュー --}}
        @if (
            !Request::is('login', 'register', 'email*', 'admin/login')
            && (Auth::guard('admin')->check() || Auth::guard('web')->check())
            )
            <ul class="header-nav__list">

                {{-- 管理者ログイン中 --}}
                @if (Auth::guard('admin')->check())
                <li>
                    <a href="/admin/attendance/list" class="list__admin-attendance-list">勤怠一覧</a>
                </li>
                <li>
                    <a href="/admin/staff/list" class="admin-staff-list">スタッフ一覧</a>
                </li>
                <li>
                    <a href="/stamp_correction_request/list" class="list__admin-stamp-correction-request">申請一覧</a>
                </li>
                <li>
                    <form action="{{ route('admin.logout') }}" method="post">
                        @csrf
                            <button class="list__logout">ログアウト</button>
                    </form>
                </li>

                {{-- 一般ユーザーログイン中--}}
                @elseif (Auth::guard('web')->check() && Auth::user()->hasVerifiedEmail())
                <li>
                    <a href="/attendance" class="list__attendance">勤怠</a>
                </li>
                <li>
                    <a href="/stamp_correction_request/list" class="list__attendance-list">勤怠一覧</a>
                </li>
                <li>
                    <a href="/stamp_correction_request/list" class="list__stamp-correction-request">申請</a>
                </li>
                <li>
                    <form action="{{ route('logout') }}" method="post">
                        @csrf
                            <button class="list__logout">ログアウト</button>
                    </form>
                </li>
                @endif
            </ul>
        @endif
    </header>
@endsection