<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sign in · Co-Auth</title>
        @include('partials.favicons')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="auth-page">
        <main class="auth-card"><a class="brand" href="{{ url('/') }}"><span class="brand-mark"><img src="{{ asset('images/logo.png') }}" alt="Co-Auth Logo" class="brand-logo-img"></span><span><strong>Co-Auth</strong><small>research studio</small></span></a><p class="eyebrow">University research workspace</p><h1>Return to your research<span>.</span></h1><p class="auth-copy">Sign in to collaborate, review drafts, and use the research assistant inside your project context.</p><form method="POST" action="{{ route('login.store') }}">@csrf<label>Email<input type="email" name="email" value="{{ old('email') }}" required autofocus></label><label>Password<input type="password" name="password" required></label><label class="remember"><input type="checkbox" name="remember"> Keep me signed in</label>@error('email')<p class="form-error">{{ $message }}</p>@enderror<button class="button button-primary" type="submit">Sign in</button></form><p class="demo-hint">Local demo: test@example.com / password</p><p class="auth-switch">New to Co-Auth? <a href="{{ route('register') }}">Create an ORCID-linked account</a></p></main>
    </body>
</html>