<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Logistics models — spec S10, U12 (unverified transporter block).
 */
class Transporter extends Model
{
    protected $fillable = ['user_id', 'status', 'verified_at'];

    protected function casts(): array
    {
        return ['verified_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }
}

class Vehicle extends Model
{
    protected $fillable = [
        'transporter_id', 'type', 'plate_number', 'capacity_kg',
        'license_doc_path', 'registration_doc_path', 'is_verified',
    ];

    protected function casts(): array
    {
        return ['is_verified' => 'boolean'];
    }

    public function transporter(): BelongsTo
    {
        return $this->belongsTo(Transporter::class);
    }
}

class TransportQuote extends Model
{
    protected $fillable = ['order_id', 'transporter_id', 'vehicle_id', 'price', 'eta_hours', 'status'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function transporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transporter_id');
    }
}

class Delivery extends Model
{
    protected $fillable = [
        'order_id', 'transport_quote_id', 'status', 'assigned_transporter_id',
        'assigned_vehicle_id', 'proof_of_delivery_path', 'picked_up_at', 'delivered_at',
    ];

    protected function casts(): array
    {
        return ['picked_up_at' => 'datetime', 'delivered_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
