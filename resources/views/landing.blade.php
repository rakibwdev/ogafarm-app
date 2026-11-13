@extends('layouts.app')

@section('title', 'Welcome')

@section('content')
<div class="rounded-2xl bg-gradient-to-r from-green-700 to-green-500 p-10 text-white">
    <h1 class="text-3xl font-bold sm:text-4xl">Nigeria's agricultural marketplace — one platform, every role.</h1>
    <p class="mt-3 max-w-2xl text-green-50">Buy produce, run your farm storefront, organize group purchases, hire verified transporters — all shareable with a single link.</p>
</div>

<div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach ([
        ['farmer', 'Farmer', 'Sell produce & run your storefront', 'Paid membership'],
        ['supplier', 'Farm Supplier', 'Sell feed, seeds, fertilizer & tools', 'Paid membership'],
        ['buyer', 'Buyer', 'Buy produce or send procurement RFQs', 'Free'],
        ['transporter', 'Transporter', 'Deliver orders with your vehicle', 'Free'],
        ['job_seeker', 'Job Seeker / Consultant', 'Find agri jobs & offer services', 'Free'],
    ] as [$role, $title, $desc, $price])
    <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold">{{ $title }}</h3>
        <p class="mt-1 text-sm text-neutral-600">{{ $desc }}</p>
        <p class="mt-3 text-xs font-semibold uppercase tracking-wide {{ $price === 'Free' ? 'text-blue-600' : 'text-green-700' }}">{{ $price }}</p>
        <a href="{{ route('register') }}?role={{ $role }}" class="mt-4 inline-block rounded-lg border border-green-600 px-4 py-2 text-sm font-semibold text-green-700 hover:bg-green-50">Get started</a>
    </div>
    @endforeach
</div>

<div class="mt-8 rounded-xl border border-neutral-200 bg-white p-6">
    <h3 class="font-semibold">Membership plans (farmers & suppliers)</h3>
    <div class="mt-3 grid gap-3 sm:grid-cols-3 text-sm">
        <div class="rounded-lg bg-neutral-50 p-3">3-Month — <strong>₦30,000</strong></div>
        <div class="rounded-lg bg-neutral-50 p-3">6-Month — <strong>₦54,000</strong></div>
        <div class="rounded-lg bg-neutral-50 p-3">12-Month — <strong>₦95,000</strong></div>
    </div>
</div>
@endsection
