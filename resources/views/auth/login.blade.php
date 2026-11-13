@extends('layouts.app')

@section('title', 'Sign in')

@section('content')
<div class="mx-auto max-w-sm">
    <h1 class="text-2xl font-bold">Sign in to OgaFarm</h1>

    <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
        @csrf
        <input type="text" name="login" value="{{ old('login') }}" placeholder="Email or phone" class="w-full rounded-lg border-neutral-300" required autofocus>
        @error('login') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

        <input type="password" name="password" placeholder="Password" class="w-full rounded-lg border-neutral-300" required>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="remember" class="accent-green-600"> Remember me
        </label>

        <button class="w-full rounded-lg bg-green-600 py-3 font-semibold text-white hover:bg-green-700">Sign in</button>
    </form>

    <p class="mt-4 text-center text-sm text-neutral-600">New to OgaFarm? <a class="text-green-700 underline" href="{{ route('register') }}">Create an account</a></p>
</div>
@endsection
