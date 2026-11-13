<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Messaging, disputes, notifications consent, audit trail.
 * Spec ref: S13; §12 audit/reconciliation; consent-gated channels.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_one_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_two_id')->constrained('users')->cascadeOnDelete();
            $table->string('context_type', 24)->nullable(); // order|rfq|product|job|support
            $table->unsignedBigInteger('context_id')->nullable();
            $table->timestampsTz();

            $table->unique(['user_one_id', 'user_two_id', 'context_type', 'context_id'], 'uq_conversation_context');
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_ai_assisted')->default(false); // S13 AI disclosure
            $table->timestampTz('read_at')->nullable();
            $table->timestampsTz();

            $table->index(['conversation_id', 'created_at']);
        });

        // Per-channel consent (transactional messages are always allowed).
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('in_app')->default(true);
            $table->boolean('whatsapp')->default(false); // opt-in marketing only
            $table->boolean('sms')->default(false);
            $table->boolean('email')->default(false);
            $table->timestampsTz();
        });

        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->string('subject_type', 24); // order|delivery|group_order
            $table->unsignedBigInteger('subject_id');
            $table->foreignId('opened_by')->constrained('users')->cascadeOnDelete();
            $table->text('reason');
            $table->string('status', 24)->default('open'); // open|under_review|resolved|closed
            $table->text('resolution')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('resolved_at')->nullable();
            $table->timestampsTz();

            $table->index(['subject_type', 'subject_id', 'status']);
        });

        // Immutable operational history (AT08).
        Schema::create('audit_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 64);
            $table->string('entity_type', 48);
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('meta')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['entity_type', 'entity_id']);
            $table->index(['actor_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
        Schema::dropIfExists('disputes');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
    }
};
