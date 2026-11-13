<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Logistics: transporter profiles, vehicles, quotes/bids, deliveries, proof of delivery.
 * Spec ref: S10; U12 (unverified transporter block); §11 transport state machine.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transporters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('status', 24)->default('pending'); // pending|verified|rejected
            $table->timestampTz('verified_at')->nullable();
            $table->timestampsTz();
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transporter_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32); // pickup_truck|box_truck|flatbed|refrigerated
            $table->string('plate_number', 24)->unique();
            $table->decimal('capacity_kg', 10, 2);
            $table->string('license_doc_path')->nullable();
            $table->string('registration_doc_path')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestampsTz();
        });

        Schema::create('transport_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transporter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('price', 12, 2);
            $table->integer('eta_hours')->nullable();
            $table->string('status', 16)->default('submitted'); // submitted|accepted|rejected|expired
            $table->timestampsTz();

            $table->index(['order_id', 'status']);
        });

        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transport_quote_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 24)->default('requested'); // requested|quoted|assigned|pickup|in_transit|delivered|failed
            $table->foreignId('assigned_transporter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->string('proof_of_delivery_path')->nullable();
            $table->timestampTz('picked_up_at')->nullable();
            $table->timestampTz('delivered_at')->nullable();
            $table->timestampsTz();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('transport_quotes');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('transporters');
    }
};
