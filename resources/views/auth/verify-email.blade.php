@extends('layouts.app')

@section('title', 'Verify email')

@section('content')
<div class="mx-auto max-w-md text-center">
    <h1 class="text-2xl font-bold">Verify your email</h1>
    <p class="mt-2 text-neutral-600">We sent a verification link to <strong>{{ $user->email }}</strong>.</p>

    @if (session('status'))
        <p class="mt-3 rounded-lg bg-green-50 px-3 py-2 text-sm text-green-800">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="mt-6">
        @csrf
        <button class="rounded-lg border border-green-600 px-4 py-2 text-sm font-semibold text-green-700 hover:bg-green-50">Resend verification email</button>
    </form>
</div>
@endsection
