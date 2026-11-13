<?php

namespace App\Services;

use App\Enums\ReferralStatus;
use App\Enums\Role;
use App\Models\Referral;
use App\Models\RewardEntitlement;
use App\Models\RewardLedger;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Referral Reward Engine — implements spec §6 binding rules and A5 transaction sequence.
 *
 * HARD RULES (server-side only; never trust client/UI counters):
 *  1. Only active paying farmers/suppliers can be rewarded.
 *  2. Referred user must be first-time, farmer/supplier role, verified, with a settled payment.
 *  3. Mandatory 14-day validation hold after settlement (no refund/fraud signal).
 *  4. Exactly 14 days granted once; max TWO lifetime grants (28 days) per verified owner.
 *  5. Lifetime cap NEVER resets on renewal, plan change, reactivation, or role change.
 *  6. Direct (depth 1) attribution only — no upstream/multi-level rewards.
 *  7. Grants are atomic + idempotent via reward_event_id and unique (referrer, referred) pair.
 */
class ReferralRewardService
{
    public const VALIDATION_HOLD_DAYS = 14;

    public const ATTRIBUTION_WINDOW_DAYS = 30;

    /** Steps 1–2 of A5: record direct attribution at signup. */
    public function attributeSignup(User $newUser, string $referralCode, string $source = 'invite_link'): ?Referral
    {
        return DB::transaction(function () use ($newUser, $referralCode, $source) {
            $referrer = User::where('referral_code', $referralCode)->lockForUpdate()->first();

            if (! $referrer) {
                return null;
            }

            // Self-referral protection (spec §11): matching owner identity.
            if ($referrer->id === $newUser->id) {
                return Referral::create([
                    'referrer_owner_id' => $referrer->id,
                    'referred_user_id' => $newUser->id,
                    'attribution_code' => $referralCode,
                    'attribution_source' => $source,
                    'attribution_at' => now(),
                    'attribution_window_expires_at' => now()->addDays(self::ATTRIBUTION_WINDOW_DAYS),
                    'direct_depth' => 1,
                    'status' => ReferralStatus::RejectedSelfReferral->value,
                    'rejection_reason' => 'Self-attribution blocked',
                ]);
            }

            // One direct referrer per new account; keep the FIRST valid attribution only.
            return Referral::firstOrCreate(
                ['referred_user_id' => $newUser->id],
                [
                    'referrer_owner_id' => $referrer->id,
                    'attribution_code' => $referralCode,
                    'attribution_source' => $source,
                    'attribution_at' => now(),
                    'attribution_window_expires_at' => now()->addDays(self::ATTRIBUTION_WINDOW_DAYS),
                    'direct_depth' => 1,
                    'status' => ReferralStatus::Registered->value,
                ]
            );
        });
    }

    /** Steps 3–4: on settled first payment of a referred farmer/supplier, arm the 14-day hold. */
    public function armQualificationHold(User $referredUser): void
    {
        Referral::where('referred_user_id', $referredUser->id)
            ->where('direct_depth', 1)
            ->where('status', ReferralStatus::Registered->value)
            ->update([
                'status' => ReferralStatus::PendingQualification->value,
                'qualification_eligible_at' => now()->addDays(self::VALIDATION_HOLD_DAYS),
            ]);
    }

    /**
     * Steps 5–7 of A5: eligibility worker — evaluate, then atomically grant 14 days once.
     * Called by the scheduler for referrals whose hold has elapsed.
     */
    public function evaluateAndGrant(Referral $referral): ?RewardLedger
    {
        return DB::transaction(function () use ($referral) {
            // Lock referrer row + entitlement row to serialize concurrent grants (AT05).
            $referrer = User::whereKey($referral->referrer_owner_id)->lockForUpdate()->first();

            $entitlement = RewardEntitlement::where('owner_id', $referrer->id)->lockForUpdate()->first()
                ?? RewardEntitlement::create(['owner_id' => $referrer->id]);

            // Rule 1: referrer must be an active paying farmer/supplier.
            $referrerEligible = $referrer->hasRole(Role::Farmer) || $referrer->hasRole(Role::Supplier);
            $referrerEligible = $referrerEligible && $referrer->hasActiveSubscription();

            // Rule 2: referred user's first farmer/supplier subscription is settled & verified.
            $referred = User::find($referral->referred_user_id);
            $referredEligible = $referred->is_identity_verified
                && ($referred->hasRole(Role::Farmer) || $referred->hasRole(Role::Supplier));
            $referredEligible = $referredEligible
                && Subscription::where('user_id', $referred->id)->whereIn('status', ['ACTIVE', 'EXPIRING'])->exists();

            // Rule 3: refund/fraud re-check before the grant (R09).
            $hasRefundSignal = $referred->paymentEvents()
                ->whereIn('status', ['refunded', 'rejected'])
                ->exists();

            $failStatus = null;
            if (! $referrerEligible) {
                $failStatus = ReferralStatus::Rejected->value; // free-role or lapsed referrer
            } elseif (! $referredEligible || $hasRefundSignal) {
                $failStatus = ReferralStatus::Rejected->value;
            } elseif (! $entitlement->hasCapRemaining()) {
                $failStatus = ReferralStatus::CapReached->value; // track forever, reward 0 (U05/R06)
            }

            if ($failStatus !== null) {
                $referral->update(['status' => $failStatus]);

                return null;
            }

            // Grant once — idempotency key from referral id; unique pair guards duplicates.
            $ledger = RewardLedger::firstOrCreate(
                [
                    'referrer_owner_id' => $referrer->id,
                    'qualifying_referral_id' => $referral->id,
                ],
                [
                    'reward_event_id' => Str::uuid()->toString(),
                    'days_granted' => RewardEntitlement::DAYS_PER_GRANT,
                    'granted_at' => now(),
                    'is_reversed' => false,
                ]
            );

            if ($ledger->wasRecentlyCreated) {
                // Atomic counter + expiry extension in the same transaction (AT03/AT04).
                $entitlement->increment('lifetime_grants_used');
                $entitlement->increment('lifetime_days_earned', RewardEntitlement::DAYS_PER_GRANT);

                $subscription = $referrer->subscriptions()
                    ->whereIn('status', ['ACTIVE', 'EXPIRING'])
                    ->orderByDesc('paid_until')
                    ->lockForUpdate()
                    ->first();

                if ($subscription) {
                    $subscription->increment('earned_extension_days', RewardEntitlement::DAYS_PER_GRANT);
                    $subscription->update([
                        'paid_until' => $subscription->paid_until->addDays(RewardEntitlement::DAYS_PER_GRANT),
                    ]);
                }

                $referral->update(['status' => ReferralStatus::Qualified->value]);

                // Notify referrer AFTER commit is queued by caller (A5 step 7).
            }

            return $ledger;
        });
    }

    /** U09/R09: reverse a granted reward on refund or fraud, with reason logged. */
    public function reverse(RewardLedger $ledger, string $reason, ?User $admin = null): RewardLedger
    {
        return DB::transaction(function () use ($ledger, $reason, $admin) {
            if ($ledger->is_reversed) {
                return $ledger; // idempotent
            }

            $entitlement = RewardEntitlement::where('owner_id', $ledger->referrer_owner_id)->lockForUpdate()->first();
            if ($entitlement) {
                $entitlement->decrement('lifetime_grants_used');
                $entitlement->decrement('lifetime_days_earned', $ledger->days_granted);
            }

            $subscription = Subscription::where('user_id', $ledger->referrer_owner_id)
                ->whereIn('status', ['ACTIVE', 'EXPIRING'])->lockForUpdate()->first();
            if ($subscription) {
                $subscription->decrement('earned_extension_days', $ledger->days_granted);
                $subscription->update(['paid_until' => $subscription->paid_until->subDays($ledger->days_granted)]);
            }

            $ledger->update([
                'is_reversed' => true,
                'reversal_reason' => $reason,
                'reversed_by' => $admin?->id,
            ]);

            return $ledger;
        });
    }
}
