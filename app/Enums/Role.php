<?php

namespace App\Enums;

/**
 * Spec §3: role entitlements matrix. Server-enforced — never client UI.
 */
enum Role: string
{
    case Farmer = 'farmer';
    case Supplier = 'supplier';
    case Buyer = 'buyer';
    case GroupBuyer = 'group_buyer';
    case DiasporaBuyer = 'diaspora_buyer';
    case Transporter = 'transporter';
    case JobSeeker = 'job_seeker';
    case Consultant = 'consultant';
    case Admin = 'admin';

    /** Paid roles require an active subscription (spec: farmers & suppliers only). */
    public function isPaid(): bool
    {
        return in_array($this, [self::Farmer, self::Supplier], true);
    }

    /** Roles eligible to earn referral rewards (active paying farmers/suppliers). */
    public function canEarnReferralRewards(): bool
    {
        return $this->isPaid();
    }
}
