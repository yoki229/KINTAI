<!DOCTYPE html>
<html class="html" lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('/css/layouts/sanitize.css')  }}">
    <link rel="stylesheet" href="{{ asset('/css/layouts/common.css')  }}">
    @yield('css')
</head>

<body class="body">
    <!-- ヘッダー -->
    @yield('header')

    <div class="app">
        <!-- コンテンツ -->
        <div class="content">
        @yield('content')
        </div>
    </div>
</body>

</html>