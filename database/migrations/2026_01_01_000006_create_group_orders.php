<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Group buying: organizer, member shares, payment split, pickup hub.
 * Spec ref: S08; min group size 4; underfunded auto-cancellation; split refunds.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('unit', 24)->default('kg');
            $table->decimal('target_quantity', 12, 2);
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->unsignedInteger('min_members')->default(4); // spec: minimum group size 4
            $table->unsignedInteger('max_members')->nullable();
            $table->timestampTz('join_deadline_at')->nullable();
            $table->string('pickup_point')->nullable();
            $table->string('status', 24)->default('open'); // open|funded|sourcing|fulfilment|completed|cancelled
            $table->string('cancellation_reason', 64)->nullable();
            $table->timestampsTz();

            $table->index('status');
        });

        Schema::create('group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('quantity_share', 12, 2);
            $table->decimal('amount_due', 14, 2)->default(0);
            $table->string('payment_status', 16)->default('unpaid'); // unpaid|paid|refunded
            $table->string('payment_reference', 191)->nullable();
            $table->timestampTz('paid_at')->nullable();
            $table->timestampsTz();

            $table->unique(['group_order_id', 'user_id']); // one membership per member
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_members');
        Schema::dropIfExists('group_orders');
    }
};
