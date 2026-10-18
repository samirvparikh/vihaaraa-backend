<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · Vihaaraa Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin/admin.css') }}?v=1">
</head>
<body>
<div class="login-wrap">
    <form class="login" method="post" action="{{ route('admin.login') }}">
        @csrf
        <span class="brand">Vihaaraa</span>
        <p class="sub">Admin panel sign in</p>
        @if ($errors->any())
            <div class="alert err">{{ $errors->first() }}</div>
        @endif
        <label class="field"><span>Email</span><input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"></label>
        <label class="field"><span>Password</span><input type="password" name="password" required autocomplete="current-password"></label>
        <label class="check"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
        <button class="btn">Sign in</button>
    </form>
</div>
</body>
</html>
