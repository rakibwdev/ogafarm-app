<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Subscription & payment layer. Server-side entitlements only.
 * Spec ref: §9 subscriptions/payments, §10 webhook rules.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plan_type', 16); // 3_MONTH|6_MONTH|12_MONTH
            $table->decimal('amount', 12, 2);
            $table->timestampTz('starts_at')->nullable();
            $table->timestampTz('paid_until')->nullable()->index();
            $table->unsignedInteger('earned_extension_days')->default(0);
            $table->string('status', 16)->default('PENDING')->index(); // PENDING|ACTIVE|EXPIRING|EXPIRED|CANCELLED
            $table->timestampsTz();

            $table->index(['user_id', 'status']);
        });

        // Every gateway settlement event, unique per provider reference (idempotency guard).
        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();
            $table->string('gateway', 24); // paystack|flutterwave
            $table->string('gateway_event_ref', 191)->unique(); // event id / tx_ref — replay protection
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('currency', 8)->default('NGN');
            $table->string('status', 24)->default('received'); // received|processed|rejected|duplicate
            $table->json('payload')->nullable();
            $table->timestampTz('processed_at')->nullable();
            $table->timestampsTz();
        });

        // Audit trail of entitlement changes (grant, renewal, reversal, expiry).
        Schema::create('entitlement_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32); // subscription_activated|renewal|reward_extension|reward_reversal|expiry
            $table->integer('days_delta')->default(0);
            $table->timestampTz('effective_paid_until')->nullable();
            $table->json('meta')->nullable();
            $table->timestampsTz();

            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entitlement_events');
        Schema::dropIfExists('payment_events');
        Schema::dropIfExists('subscriptions');
    }
};
