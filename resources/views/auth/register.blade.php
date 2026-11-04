@extends('layouts.auth')
@section('title', 'Create account')

@section('content')
<h1 class="font-serif text-4xl mb-2">Create account</h1>
<p class="text-slate-500 mb-8">It takes 30 seconds.</p>

<form method="POST" action="{{ route('register') }}" class="space-y-4">
    @csrf

    <div>
        <label class="text-sm font-semibold">Full name</label>
        <input name="name" value="{{ old('name') }}" required autofocus
               class="mt-1 w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 focus:border-teal-500 focus:bg-white outline-none transition">
        @error('name')<div class="text-sm text-coral-500 mt-1">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="text-sm font-semibold">Email</label>
        <input name="email" type="email" value="{{ old('email') }}" required
               class="mt-1 w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 focus:border-teal-500 focus:bg-white outline-none transition">
        @error('email')<div class="text-sm text-coral-500 mt-1">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="text-sm font-semibold">Password</label>
        <input name="password" type="password" required
               class="mt-1 w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 focus:border-teal-500 focus:bg-white outline-none transition">
        @error('password')<div class="text-sm text-coral-500 mt-1">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="text-sm font-semibold">Confirm password</label>
        <input name="password_confirmation" type="password" required
               class="mt-1 w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 focus:border-teal-500 focus:bg-white outline-none transition">
    </div>

    <button class="w-full py-3.5 rounded-2xl bg-teal-700 text-white font-bold hover:shadow-glow transition">
        Create account
    </button>
</form>

<div class="mt-6 text-center text-sm text-slate-500">
    Already have an account?
    <a href="{{ route('login') }}" class="text-teal-800 font-semibold u-link">Sign in</a>
</div>
@endsection
