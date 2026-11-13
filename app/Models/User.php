<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'phone_number', 'password', 'referral_code', 'locale',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_identity_verified' => 'boolean',
        ];
    }

    // ── Roles (server-enforced, multi-role capable — spec §4 routing rules) ──

    public function roles(): BelongsToMany
    {
        // user_roles join table carries is_active; expose as pivot.
        return $this->belongsToMany(Role::class->value, 'user_roles', 'user_id', 'role_name')
            ->withPivot('is_active')->wherePivot('is_active', true);
    }

    public function roleRecords(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    public function hasRole(Role $role): bool
    {
        return $this->roleRecords()->where('role_name', $role->value)->where('is_active', true)->exists();
    }

    public function assignRole(Role $role): void
    {
        $this->roleRecords()->firstOrCreate(['role_name' => $role->value], ['is_active' => true]);
    }

    // ── Identity verification (KYC — spec §12) ──

    public function verifications(): HasMany
    {
        return $this->hasMany(Verification::class);
    }

    public function isFullyVerified(): bool
    {
        return $this->is_identity_verified;
    }

    // ── Subscription & payments ──

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->whereIn('status', ['ACTIVE', 'EXPIRING'])->latestOfMany();
    }

    /** True when a paid role has a live entitlement (paid_until >= now). */
    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription()
            ->where('paid_until', '>=', now())
            ->exists();
    }

    public function paymentEvents(): HasMany
    {
        return $this->hasMany(PaymentEvent::class);
    }

    // ── Referral engine (spec §6) ──

    public function referralsMade(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_owner_id');
    }

    public function referralAttribution(): HasOne
    {
        return $this->hasOne(Referral::class, 'referred_user_id');
    }

    public function rewardEntitlement(): HasOne
    {
        return $this->hasOne(RewardEntitlement::class, 'owner_id');
    }

    /** Generate a permanent unique referral code (OGA-XXXXXX). */
    public function generateReferralCode(): string
    {
        do {
            $code = 'OGA-'.strtoupper(Str::random(6));
        } while (self::where('referral_code', $code)->exists());

        $this->update(['referral_code' => $code]);

        return $code;
    }

    // ── Marketplace ──

    public function storefronts(): HasMany
    {
        return $this->hasMany(Storefront::class, 'owner_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function transportProfile(): HasOne
    {
        return $this->hasOne(Transporter::class);
    }

    // ── Ops ──

    public function notificationPreference(): HasOne
    {
        return $this->hasOne(NotificationPreference::class);
    }

    public function auditEvents(): HasMany
    {
        return $this->hasMany(AuditEvent::class, 'actor_id');
    }
}
