@extends('layouts.app')

@section('title', 'Choose your plan')

@section('content')
<div class="mx-auto max-w-3xl">
    <h1 class="text-2xl font-bold">Activate your membership</h1>
    <p class="mt-1 text-neutral-600">Farmers and suppliers need an active subscription to publish listings and receive paid-role benefits.</p>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        @foreach (\App\Enums\SubscriptionPlan::cases() as $plan)
            <div class="rounded-xl border-2 border-neutral-200 bg-white p-6 text-center hover:border-green-500">
                <h3 class="text-lg font-semibold">{{ $plan->label() }}</h3>
                <p class="mt-2 text-3xl font-bold text-green-700">₦{{ number_format($plan->price()) }}</p>
                <p class="mt-1 text-xs text-neutral-500">{{ $plan->label() }} access • rewards stack the same across plans</p>
                <button disabled class="mt-4 w-full rounded-lg bg-neutral-200 py-2 text-sm font-semibold text-neutral-400" title="Checkout arrives with Milestone 3">Checkout soon</button>
            </div>
        @endforeach
    </div>
</div>
@endsection
