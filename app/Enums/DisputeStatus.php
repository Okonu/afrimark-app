<?php

namespace App\Enums;

enum DisputeStatus: string
{
    case PENDING = 'pending';
    case UNDER_REVIEW = 'under_review';
    case RESOLVED_APPROVED = 'resolved_approved';
    case RESOLVED_REJECTED = 'resolved_rejected';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::UNDER_REVIEW => 'Under Review',
            self::RESOLVED_APPROVED => 'Resolved (Approved)',
            self::RESOLVED_REJECTED => 'Resolved (Rejected)',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::UNDER_REVIEW => 'primary',
            self::RESOLVED_APPROVED => 'success',
            self::RESOLVED_REJECTED => 'danger',
        };
    }
}
