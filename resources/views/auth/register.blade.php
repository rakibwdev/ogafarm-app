@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="mx-auto max-w-md">
    <h1 class="text-2xl font-bold">Create your OgaFarm account</h1>

    @if (session('referral_code') ?? ($referralCode ?? null))
        <p class="mt-2 rounded-lg bg-green-50 px-3 py-2 text-sm text-green-800">Invitation applied — <strong>{{ $referralCode }}</strong></p>
    @endif

    <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label class="mb-1 block text-sm font-medium">I am a…</label>
            <div class="grid grid-cols-2 gap-2">
                @foreach (['farmer' => 'Farmer', 'supplier' => 'Farm Supplier', 'buyer' => 'Buyer', 'transporter' => 'Transporter', 'job_seeker' => 'Job Seeker'] as $value => $label)
                    <label class="flex cursor-pointer items-center gap-2 rounded-lg border p-3 text-sm has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                        <input type="radio" name="role" value="{{ $value }}" {{ old('role', request('role')) === $value ? 'checked' : '' }} class="accent-green-600" required>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
            @error('role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <input type="text" name="name" value="{{ old('name') }}" placeholder="Full name" class="w-full rounded-lg border-neutral-300" required autofocus>
        @error('name') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

        <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" class="w-full rounded-lg border-neutral-300" required>
        @error('email') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

        <input type="tel" name="phone_number" value="{{ old('phone_number') }}" placeholder="Phone number" class="w-full rounded-lg border-neutral-300" required>
        @error('phone_number') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

        <input type="password" name="password" placeholder="Password" class="w-full rounded-lg border-neutral-300" required>
        @error('password') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

        <input type="password" name="password_confirmation" placeholder="Confirm password" class="w-full rounded-lg border-neutral-300" required>

        <label class="flex items-start gap-2 text-sm text-neutral-600">
            <input type="checkbox" name="terms" class="mt-1 accent-green-600" required>
            I agree to the OgaFarm terms and consent to transactional messages.
        </label>
        @error('terms') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

        <button class="w-full rounded-lg bg-green-600 py-3 font-semibold text-white hover:bg-green-700">Create account</button>
    </form>

    <p class="mt-4 text-center text-sm text-neutral-600">Already a member? <a class="text-green-700 underline" href="{{ route('login') }}">Sign in</a></p>
</div>
@endsection
