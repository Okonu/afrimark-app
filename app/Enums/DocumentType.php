<?php

namespace App\Enums;

enum DocumentType: string
{
    case REGISTRATION = 'registration';
    case TAX = 'tax';
    case LICENSE = 'license';

    public function label(): string
    {
        return match($this) {
            self::REGISTRATION => 'Registration Document',
            self::TAX => 'Tax Document',
            self::LICENSE => 'Business License',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::REGISTRATION => 'Official business registration certificate',
            self::TAX => 'Tax registration or clearance certificate',
            self::LICENSE => 'Business operating license',
        };
    }

    public function isRequired(): bool
    {
        return match($this) {
            self::REGISTRATION => true,
            self::TAX => true,
            self::LICENSE => false,
        };
    }
}
