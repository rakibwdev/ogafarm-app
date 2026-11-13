<div>
    <h1 class="text-2xl font-bold">{{ $title }}</h1>
    <p class="mt-1 text-neutral-600">Welcome back, {{ $user->name }}.</p>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($cards as $card)
            <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm">
                <h3 class="font-semibold">{{ $card[0] }}</h3>
                <p class="mt-1 text-sm text-neutral-600">{{ $card[1] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 rounded-xl border border-green-200 bg-green-50 p-5">
        <h3 class="font-semibold text-green-800">Invite & Share</h3>
        <p class="mt-1 text-sm text-green-700">Your referral code: <strong>{{ $user->referral_code ?? '—' }}</strong> • Invite link: farmoga.com/invite/{{ $user->referral_code ?? '…' }}</p>
        @if ($user->hasActiveSubscription())
            <p class="mt-2 text-sm text-green-700">Membership active — rewards: {{ $user->rewardEntitlement?->lifetime_grants_used ?? 0 }}/2 lifetime slots</p>
        @endif
    </div>
</div>
