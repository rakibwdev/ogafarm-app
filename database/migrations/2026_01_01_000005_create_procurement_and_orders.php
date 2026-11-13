<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Procurement: buyer RFQs, supplier quotes, orders & order lines.
 * Spec ref: S06–S07, §9 rfqs/quotes/orders/order_items, §11 order state machine.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('specification')->nullable();
            $table->string('unit', 24)->default('kg');
            $table->decimal('quantity', 12, 2);
            $table->string('destination')->nullable();
            $table->date('fulfillment_deadline')->nullable();
            $table->string('status', 16)->default('draft'); // draft|open|quoted|accepted|ordered|closed
            $table->timestampTz('expires_at')->nullable();
            $table->timestampsTz();

            $table->index(['buyer_id', 'status']);
        });

        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 14, 2);
            $table->integer('eta_days')->nullable();
            $table->text('transport_terms')->nullable();
            $table->string('status', 16)->default('submitted'); // submitted|withdrawn|accepted|rejected|expired
            $table->timestampTz('expires_at')->nullable();
            $table->timestampsTz();

            $table->index(['rfq_id', 'status']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('rfq_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 24)->default('CREATED')->index();
            // CREATED|PAYMENT_PENDING|CONFIRMED|PREPARING|DISPATCHED|DELIVERED|DISPUTED|CANCELLED|REFUNDED
            $table->string('payment_reference', 191)->nullable()->index(); // gateway tx ref
            $table->decimal('produce_total', 14, 2)->default(0);
            $table->decimal('service_fee', 12, 2)->default(0);
            $table->decimal('transport_fee', 12, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->string('fulfillment_type', 16)->default('delivery'); // delivery|pickup
            $table->text('delivery_address')->nullable();
            $table->json('recipient_details')->nullable(); // diaspora recipients (S06)
            $table->timestampsTz();

            $table->index(['buyer_id', 'status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 14, 2);
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('quotes');
        Schema::dropIfExists('rfqs');
    }
};
