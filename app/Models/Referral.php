<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Referral engine models — spec §6, R01–R12, AT02–AT07.
 * Grant logic lives in app/Services/ReferralRewardService.php (atomic, server-side only).
 */
class Referral extends Model
{
    protected $fillable = [
        'referrer_owner_id', 'referred_user_id', 'attribution_code', 'attribution_source',
        'attribution_at', 'attribution_window_expires_at', 'direct_depth', 'status',
        'qualification_eligible_at', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'attribution_at' => 'datetime',
            'attribution_window_expires_at' => 'datetime',
            'qualification_eligible_at' => 'datetime',
            'direct_depth' => 'integer',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_owner_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(RewardLedger::class, 'qualifying_referral_id');
    }
}

class RewardLedger extends Model
{
    protected $fillable = [
        'reward_event_id', 'referrer_owner_id', 'qualifying_referral_id', 'days_granted',
        'granted_at', 'is_reversed', 'reversal_reason', 'reversal_notes', 'reversed_by',
    ];

    protected function casts(): array
    {
        return ['granted_at' => 'datetime', 'is_reversed' => 'boolean'];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_owner_id');
    }

    public function qualifyingReferral(): BelongsTo
    {
        return $this->belongsTo(Referral::class, 'qualifying_referral_id');
    }
}

class RewardEntitlement extends Model
{
    /** Hard lifetime cap from spec §6 rule 5. */
    public const LIFETIME_GRANT_LIMIT = 2;

    public const LIFETIME_DAY_LIMIT = 28;

    public const DAYS_PER_GRANT = 14;

    protected $fillable = ['owner_id', 'lifetime_grants_used', 'lifetime_days_earned'];

    protected function casts(): array
    {
        return ['lifetime_grants_used' => 'integer', 'lifetime_days_earned' => 'integer'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function hasCapRemaining(): bool
    {
        return $this->lifetime_grants_used < self::LIFETIME_GRANT_LIMIT;
    }
}

class ShareEvent extends Model
{
    public $timestamps = false;

    protected $fillable = ['owner_id', 'content_type', 'content_id', 'channel', 'ip_hash', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }
}
