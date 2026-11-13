<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Storefronts & catalogue: farms, suppliers, products, rescue produce, inventory.
 * Spec ref: S03–S05, S09; §9 storefronts/products/inventory_events.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storefronts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 16)->default('farm'); // farm|supplier
            $table->string('slug', 191)->unique();
            $table->string('name');
            $table->text('bio')->nullable();
            $table->string('locality')->nullable();
            $table->string('operating_hours')->nullable();
            $table->string('cover_image_path')->nullable();
            $table->boolean('is_published')->default(false);
            $table->string('moderation_status', 24)->default('approved'); // pending|approved|held
            $table->timestampsTz();

            $table->index(['type', 'is_published', 'moderation_status']);
        });

        // Up to five configurable category tabs per storefront (spec S03).
        Schema::create('storefront_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('storefront_id')->constrained()->cascadeOnDelete();
            $table->string('name', 64);
            $table->unsignedTinyInteger('position')->default(1);
            $table->timestampsTz();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('storefront_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('storefront_categories')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category', 64)->nullable(); // produce|feed|seed|fertilizer|tools|livestock_supplies
            $table->string('unit', 24)->default('kg'); // kg|bag|crate|...
            $table->decimal('unit_price', 12, 2);
            $table->decimal('available_stock', 12, 2)->default(0);
            $table->string('stock_status', 16)->default('IN_STOCK'); // IN_STOCK|LOW_STOCK|OUT_OF_STOCK|RESERVED
            $table->json('image_paths')->nullable();
            $table->string('pickup_location')->nullable();
            $table->date('available_from')->nullable();
            $table->date('available_to')->nullable();
            $table->json('delivery_options')->nullable(); // pickup|local_delivery|transport_required
            // Rescue & imperfect produce (S09)
            $table->boolean('is_rescue')->default(false);
            $table->string('condition_label', 64)->nullable(); // grade B / cosmetic imperfection
            $table->timestampTz('pickup_deadline_at')->nullable(); // urgent collection window
            $table->boolean('published')->default(false);
            $table->string('moderation_status', 24)->default('approved');
            $table->timestampsTz();
            $table->softDeletes();

            $table->index(['storefront_id', 'published', 'is_rescue']);
            $table->index('stock_status');
        });

        // Atomic stock movement audit (reservations prevent overselling — U13/AT05).
        Schema::create('inventory_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('delta', 12, 2); // negative = reserved/sold
            $table->string('reason', 32); // listing|reservation|release|sale|waste|expiry
            $table->unsignedBigInteger('order_id')->nullable()->index(); // no FK: orders table created later (audit log only)
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_events');
        Schema::dropIfExists('products');
        Schema::dropIfExists('storefront_categories');
        Schema::dropIfExists('storefronts');
    }
};
