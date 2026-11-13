<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Procurement models — spec S06–S07, §11 order state machine.
 */
class Rfq extends Model
{
    protected $fillable = [
        'buyer_id', 'title', 'specification', 'unit', 'quantity',
        'destination', 'fulfillment_deadline', 'status', 'expires_at',
    ];

    protected function casts(): array
    {
        return ['fulfillment_deadline' => 'date', 'expires_at' => 'datetime'];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }
}

class Quote extends Model
{
    protected $fillable = [
        'rfq_id', 'seller_id', 'unit_price', 'total_price', 'eta_days',
        'transport_terms', 'status', 'expires_at',
    ];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

class Order extends Model
{
    protected $fillable = [
        'buyer_id', 'rfq_id', 'status', 'payment_reference', 'produce_total',
        'service_fee', 'transport_fee', 'grand_total', 'fulfillment_type',
        'delivery_address', 'recipient_details',
    ];

    protected function casts(): array
    {
        return ['recipient_details' => 'array'];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    /** Legal transitions per spec §11 state machine. */
    public function canTransitionTo(string $to): bool
    {
        $map = [
            'CREATED' => ['PAYMENT_PENDING', 'CANCELLED'],
            'PAYMENT_PENDING' => ['CONFIRMED', 'CANCELLED'],
            'CONFIRMED' => ['PREPARING', 'REFUNDED'],
            'PREPARING' => ['DISPATCHED', 'REFUNDED'],
            'DISPATCHED' => ['DELIVERED', 'DISPUTED'],
            'DELIVERED' => [],
            'DISPUTED' => ['DELIVERED', 'REFUNDED'],
            'CANCELLED' => [],
            'REFUNDED' => [],
        ];

        return in_array($to, $map[$this->status] ?? [], true);
    }
}

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'seller_id', 'quantity', 'unit_price', 'line_total'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:2', 'unit_price' => 'decimal:2', 'line_total' => 'decimal:2'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
