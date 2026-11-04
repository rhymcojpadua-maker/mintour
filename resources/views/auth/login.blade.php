@extends('layouts.auth')
@section('title', 'Sign in')
@section('auth_image', asset('images/login-victoria.png'))

@section('content')
<h1 class="font-serif text-4xl mb-2">Welcome back</h1>
<p class="text-slate-500 mb-8">Sign in to continue your journey.</p>

<form method="POST" action="{{ route('login') }}" class="space-y-4">
    @csrf

    <div>
        <label class="text-sm font-semibold">Email</label>
        <input name="email" type="email" value="{{ old('email') }}" required autofocus
               class="mt-1 w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 focus:border-teal-500 focus:bg-white outline-none transition">
        @error('email')<div class="text-sm text-coral-500 mt-1">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="text-sm font-semibold">Password</label>
        <input name="password" type="password" required
               class="mt-1 w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 focus:border-teal-500 focus:bg-white outline-none transition">
        @error('password')<div class="text-sm text-coral-500 mt-1">{{ $message }}</div>@enderror
    </div>

    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="remember" class="rounded border-slate-300 text-teal-700 focus:ring-teal-500">
        Remember me
    </label>

    <button class="w-full py-3.5 rounded-2xl bg-teal-700 text-white font-bold hover:shadow-glow transition">
        Sign in
    </button>
</form>

<div class="mt-6 text-center text-sm text-slate-500">
    Don't have an account?
    <a href="{{ route('register') }}" class="text-teal-800 font-semibold u-link">Sign up</a>
</div>
@endsection
