<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') · Vihaaraa Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin/admin.css') }}?v=1">
</head>
<body>
@php
    $nav = [
        'Overview' => [
            ['admin.dashboard', 'Dashboard', 'admin.dashboard'],
        ],
        'Storefront' => [
            ['admin.announcements.index', 'Top Offer Bar', 'admin.announcements.*'],
            ['admin.slides.index', 'Image Slider', 'admin.slides.*'],
            ['admin.home-items.index', 'Home Sections', 'admin.home-items.*', ['section' => 'circles']],
            ['admin.pages.index', 'Pages', 'admin.pages.*'],
        ],
        'Catalogue' => [
            ['admin.products.index', 'Products', 'admin.products.*'],
            ['admin.categories.index', 'Categories', 'admin.categories.*'],
            ['admin.collections.index', 'Collections', 'admin.collections.*'],
        ],
        'Sales' => [
            ['admin.orders.index', 'Orders', 'admin.orders.*'],
            ['admin.coupons.index', 'Coupon Codes', 'admin.coupons.*'],
            ['admin.users.index', 'Customers & Admins', 'admin.users.*'],
            ['admin.subscribers.index', 'Newsletter', 'admin.subscribers.*'],
        ],
        'Configuration' => [
            ['admin.settings.edit', 'Shop Settings', 'admin.settings.*'],
            ['admin.profile.edit', 'My Account', 'admin.profile.*'],
        ],
    ];
@endphp
<div class="shell">
    <aside class="sidebar">
        <a href="{{ route('admin.dashboard') }}" class="brand" style="padding-left:22px">Vihaaraa</a>
        <div class="brand-sub">Admin Panel</div>
        @foreach ($nav as $group => $links)
            <h4>{{ $group }}</h4>
            @foreach ($links as $link)
                <a href="{{ route($link[0], $link[3] ?? []) }}" class="{{ request()->routeIs($link[2]) ? 'active' : '' }}">{{ $link[1] }}</a>
            @endforeach
        @endforeach
    </aside>
    <div class="main">
        <div class="topbar">
            <button class="btn outline sm menu-toggle" type="button" onclick="document.body.classList.toggle('nav-open')">☰ Menu</button>
            <a href="{{ config('app.frontend_url') }}" target="_blank" rel="noopener">View storefront ↗</a>
            <div style="display:flex;gap:12px;align-items:center">
                <span class="who">{{ auth()->user()->name }}</span>
                <form method="post" action="{{ route('admin.logout') }}">@csrf<button class="btn outline sm">Log out</button></form>
            </div>
        </div>
        <main class="content">
            @if (session('ok'))
                <div class="alert ok">{{ session('ok') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert err">
                    <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
<script>
    document.addEventListener('submit', function (e) {
        var msg = e.target.getAttribute('data-confirm');
        if (msg && !confirm(msg)) e.preventDefault();
    });
</script>
</body>
</html>
