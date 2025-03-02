<?php

namespace App\Enums;

enum DebtorStatus: string
{
    case PENDING = 'pending';
    case PARTIAL = 'partial';
    case PAID = 'paid';

    const ACTIVE = 'active';
    const DISPUTED = 'disputed';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::PARTIAL => 'Partially Paid',
            self::PAID => 'Paid',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'danger',
            self::PARTIAL => 'warning',
            self::PAID => 'success',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::PENDING => 'heroicon-o-clock',
            self::PARTIAL => 'heroicon-o-banknotes',
            self::PAID => 'heroicon-o-check-circle',
        };
    }
}
