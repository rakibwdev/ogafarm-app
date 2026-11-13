<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserRole extends Model
{
    protected $fillable = ['user_id', 'role_name', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

class Verification extends Model
{
    protected $fillable = ['user_id', 'type', 'document_path', 'status', 'review_notes', 'reviewed_by', 'reviewed_at'];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}

class Subscription extends Model
{
    protected $fillable = [
        'user_id', 'plan_type', 'amount', 'starts_at', 'paid_until',
        'earned_extension_days', 'status',
    ];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'paid_until' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentEvents(): HasMany
    {
        return $this->hasMany(PaymentEvent::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['ACTIVE', 'EXPIRING'], true)
            && $this->paid_until !== null
            && $this->paid_until->gte(now());
    }
}

class PaymentEvent extends Model
{
    protected $fillable = [
        'gateway', 'gateway_event_ref', 'user_id', 'subscription_id',
        'amount', 'currency', 'status', 'payload', 'processed_at',
    ];

    protected function casts(): array
    {
        return ['payload' => 'array', 'processed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}

class EntitlementEvent extends Model
{
    protected $fillable = ['user_id', 'type', 'days_delta', 'effective_paid_until', 'meta'];

    protected function casts(): array
    {
        return ['meta' => 'array', 'effective_paid_until' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
