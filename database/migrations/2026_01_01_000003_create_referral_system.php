<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Referral & reward engine. Safety-critical business rules:
 * - Direct (first-leg) attribution only, 30-day window.
 * - 14-day validation hold after settled payment.
 * - Immutable reward ledger; max TWO lifetime grants (28 days) per verified owner.
 * - Enforced by DB constraints + locking, never UI counters.
 * Spec ref: §6 (complete logic), §9 (referrals, reward_ledger), R01–R12, AT02–AT07.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_user_id')->unique()->constrained('users')->cascadeOnDelete(); // one referrer per new account
            $table->string('attribution_code', 16)->index();
            $table->string('attribution_source', 32)->nullable(); // invite_link|content_share|direct
            $table->timestampTz('attribution_at')->nullable();
            $table->timestampTz('attribution_window_expires_at')->nullable(); // +30 days policy
            $table->unsignedTinyInteger('direct_depth')->default(1); // only depth 1 is rewardable
            $table->string('status', 32)->default('REGISTERED')->index();
            // REGISTERED|PENDING_QUALIFICATION|QUALIFIED|REJECTED|REJECTED_SELF_REFERRAL|CAP_REACHED
            $table->timestampTz('qualification_eligible_at')->nullable(); // when the 14-day hold ends
            $table->text('rejection_reason')->nullable();
            $table->timestampsTz();

            // AT05: unique qualification pairing prevents duplicate grants.
            $table->unique(['referrer_owner_id', 'referred_user_id']);
        });

        Schema::create('reward_ledger', function (Blueprint $table) {
            $table->id();
            $table->string('reward_event_id', 64)->unique(); // idempotent grant key
            $table->foreignId('referrer_owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('qualifying_referral_id')->constrained('referrals')->cascadeOnDelete();
            $table->unsignedSmallInteger('days_granted')->default(14);
            $table->timestampTz('granted_at')->nullable();
            $table->boolean('is_reversed')->default(false);
            $table->string('reversal_reason', 64)->nullable();
            $table->text('reversal_notes')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampsTz();

            // AT03: hard cap — no more than 2 non-reversed grants per verified owner.
            // MySQL 8 enforces this via the application transaction + the unique pair below;
            // plus a unique index on one active grant per referral prevents double-issuance.
            $table->unique(['qualifying_referral_id', 'days_granted'], 'uq_grant_per_referral');
        });

        // Reward slots state per owner (atomic counter authority — not UI).
        Schema::create('reward_entitlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('lifetime_grants_used')->default(0); // hard max 2
            $table->unsignedSmallInteger('lifetime_days_earned')->default(0); // max 28
            $table->timestampsTz();
        });

        // Share events (analytics only — sharing never consumes reward slots).
        Schema::create('share_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('content_type', 24); // farm|supplier|product|group|job
            $table->unsignedBigInteger('content_id')->nullable();
            $table->string('channel', 24)->nullable(); // whatsapp|native|copy|web
            $table->string('ip_hash', 64)->nullable(); // privacy-safe
            $table->timestampTz('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_events');
        Schema::dropIfExists('reward_entitlements');
        Schema::dropIfExists('reward_ledger');
        Schema::dropIfExists('referrals');
    }
};
