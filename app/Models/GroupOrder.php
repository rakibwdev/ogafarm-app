<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Group buying — spec S08 (min 4 members, split payment, pickup hub).
 */
class GroupOrder extends Model
{
    protected $fillable = [
        'organizer_id', 'product_id', 'title', 'description', 'unit',
        'target_quantity', 'unit_price', 'min_members', 'max_members',
        'join_deadline_at', 'pickup_point', 'status', 'cancellation_reason',
    ];

    protected function casts(): array
    {
        return ['join_deadline_at' => 'datetime'];
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(GroupMember::class);
    }

    public function fundedTotal(): float
    {
        return (float) $this->members()->where('payment_status', 'paid')->sum('amount_due');
    }

    public function isFundingComplete(): bool
    {
        return $this->members()->count() >= $this->min_members
            && $this->members()->where('payment_status', 'paid')->count() === $this->members()->count();
    }
}

class GroupMember extends Model
{
    protected $fillable = [
        'group_order_id', 'user_id', 'quantity_share', 'amount_due',
        'payment_status', 'payment_reference', 'paid_at',
    ];

    protected function casts(): array
    {
        return ['paid_at' => 'datetime'];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(GroupOrder::class, 'group_order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
