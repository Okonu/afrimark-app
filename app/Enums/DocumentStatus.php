<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case PENDING = 'pending';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending Verification',
            self::VERIFIED => 'Verified',
            self::REJECTED => 'Rejected',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::VERIFIED => 'success',
            self::REJECTED => 'danger',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::PENDING => 'heroicon-o-clock',
            self::VERIFIED => 'heroicon-o-check-circle',
            self::REJECTED => 'heroicon-o-x-circle',
        };
    }
}
