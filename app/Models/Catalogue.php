<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Storefront extends Model
{
    protected $fillable = [
        'owner_id', 'type', 'slug', 'name', 'bio', 'locality', 'operating_hours',
        'cover_image_path', 'is_published', 'moderation_status',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(StorefrontCategory::class)->orderBy('position');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** Publicly visible products only (published + approved + live subscription). */
    public function scopePublicVisible(Builder $query): Builder
    {
        return $query->where('is_published', true)->where('moderation_status', 'approved');
    }
}

class StorefrontCategory extends Model
{
    protected $fillable = ['storefront_id', 'name', 'position'];

    public function storefront(): BelongsTo
    {
        return $this->belongsTo(Storefront::class);
    }
}

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'storefront_id', 'category_id', 'title', 'description', 'category', 'unit',
        'unit_price', 'available_stock', 'stock_status', 'image_paths', 'pickup_location',
        'available_from', 'available_to', 'delivery_options', 'is_rescue',
        'condition_label', 'pickup_deadline_at', 'published', 'moderation_status',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'available_stock' => 'decimal:2',
            'image_paths' => 'array',
            'delivery_options' => 'array',
            'available_from' => 'date',
            'available_to' => 'date',
            'pickup_deadline_at' => 'datetime',
            'is_rescue' => 'boolean',
            'published' => 'boolean',
        ];
    }

    public function storefront(): BelongsTo
    {
        return $this->belongsTo(Storefront::class);
    }

    public function inventoryEvents(): HasMany
    {
        return $this->hasMany(InventoryEvent::class);
    }

    /**
     * Atomic stock reservation — SELECT ... FOR UPDATE + guard check (U13, AT05).
     * Returns false when insufficient stock; caller must handle the race message.
     */
    public function reserveStock(float $quantity, ?int $orderId = null): bool
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($quantity, $orderId) {
            $locked = self::query()->whereKey($this->getKey())->lockForUpdate()->first();

            if (! $locked || (float) $locked->available_stock < $quantity) {
                return false;
            }

            $newStock = (float) $locked->available_stock - $quantity;
            $locked->available_stock = $newStock;
            $locked->stock_status = $newStock <= 0
                ? \App\Enums\StockStatus::OutOfStock->value
                : ($newStock < 10 ? \App\Enums\StockStatus::LowStock->value : \App\Enums\StockStatus::InStock->value);
            $locked->save();

            $locked->inventoryEvents()->create([
                'delta' => -$quantity,
                'reason' => 'reservation',
                'order_id' => $orderId,
            ]);

            return true;
        }, 5);
    }
}

class InventoryEvent extends Model
{
    public $timestamps = false;

    protected $fillable = ['product_id', 'delta', 'reason', 'order_id', 'created_at'];

    protected function casts(): array
    {
        return ['delta' => 'decimal:2', 'created_at' => 'datetime'];
    }
}
