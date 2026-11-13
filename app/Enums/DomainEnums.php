<?php

namespace App\Enums;

/**
 * Spec §6: binding referral business rules.
 */
enum ReferralStatus: string
{
    case Registered = 'REGISTERED';
    case PendingQualification = 'PENDING_QUALIFICATION';
    case Qualified = 'QUALIFIED';
    case Rejected = 'REJECTED';
    case RejectedSelfReferral = 'REJECTED_SELF_REFERRAL';
    case CapReached = 'CAP_REACHED';

    public function isRewardable(): bool
    {
        return $this === self::Qualified;
    }
}

enum SubscriptionPlan: string
{
    case ThreeMonth = '3_MONTH';
    case SixMonth = '6_MONTH';
    case TwelveMonth = '12_MONTH';

    public function price(): float
    {
        return match ($this) {
            self::ThreeMonth => 30000.00,
            self::SixMonth => 54000.00,
            self::TwelveMonth => 95000.00,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::ThreeMonth => '3-Month',
            self::SixMonth => '6-Month',
            self::TwelveMonth => '12-Month',
        };
    }
}

enum SubscriptionStatus: string
{
    case Pending = 'PENDING';
    case Active = 'ACTIVE';
    case Expiring = 'EXPIRING';
    case Expired = 'EXPIRED';
    case Cancelled = 'CANCELLED';
}

enum OrderStatus: string
{
    case Created = 'CREATED';
    case PaymentPending = 'PAYMENT_PENDING';
    case Confirmed = 'CONFIRMED';
    case Preparing = 'PREPARING';
    case Dispatched = 'DISPATCHED';
    case Delivered = 'DELIVERED';
    case Disputed = 'DISPUTED';
    case Cancelled = 'CANCELLED';
    case Refunded = 'REFUNDED';
}

enum StockStatus: string
{
    case InStock = 'IN_STOCK';
    case LowStock = 'LOW_STOCK';
    case OutOfStock = 'OUT_OF_STOCK';
    case Reserved = 'RESERVED';
}
