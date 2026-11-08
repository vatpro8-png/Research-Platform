<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Create account · Co-Auth</title>
        @include('partials.favicons')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="auth-page">
        <main class="auth-card"><a class="brand" href="{{ route('login') }}"><span class="brand-mark"><img src="{{ asset('images/logo.png') }}" alt="Co-Auth Logo" class="brand-logo-img"></span><span><strong>Co-Auth</strong><small>research studio</small></span></a><p class="eyebrow">Research identity</p><h1>Start with your scholarly identity<span>.</span></h1><p class="auth-copy">Every account is linked to an ORCID iD so authorship and collaboration stay attributable.</p><form method="POST" action="{{ route('register.store') }}">@csrf<label>Name<input type="text" name="name" value="{{ old('name') }}" required autofocus></label><label>Academic email<input type="email" name="email" value="{{ old('email') }}" required></label><label>ORCID iD<input type="text" name="orcid_id" value="{{ old('orcid_id') }}" placeholder="0000-0002-1825-0097" required></label><label>Password<input type="password" name="password" required></label><label>Confirm password<input type="password" name="password_confirmation" required></label><label class="remember"><input type="checkbox" name="is_teacher" value="1"> I am a teacher or supervisor</label>@if ($errors->any())<p class="form-error">{{ $errors->first() }}</p>@endif<button class="button button-primary" type="submit">Create account</button></form><p class="auth-switch">Already registered? <a href="{{ route('login') }}">Sign in</a></p></main>
    </body>
</html>