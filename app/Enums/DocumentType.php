<?php

namespace App\Enums;

enum DocumentType: string
{
    case REGISTRATION = 'registration';
    case TAX = 'tax';
    case LICENSE = 'license';
    case CERTIFICATE_OF_INCORPORATION = 'certificate_of_incorporation';
    case TAX_PIN = 'tax_pin';
    case CR12_CR13 = 'cr12_cr13';

    public function label(): string
    {
        return match($this) {
            self::REGISTRATION => 'Registration Document',
            self::TAX => 'Tax Document',
            self::LICENSE => 'Business License',
            self::CERTIFICATE_OF_INCORPORATION => 'Certificate of Incorporation',
            self::TAX_PIN => 'Tax PIN Document',
            self::CR12_CR13 => 'CR12 or CR13 Document',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::REGISTRATION => 'Official business registration certificate',
            self::TAX => 'Tax registration or clearance certificate',
            self::LICENSE => 'Business operating license',
            self::CERTIFICATE_OF_INCORPORATION => 'Official certificate of incorporation',
            self::TAX_PIN => 'Tax PIN registration document',
            self::CR12_CR13 => 'Company CR12 or CR13 document',
        };
    }

    public function isRequired(): bool
    {
        return match($this) {
            self::REGISTRATION => true,
            self::TAX => true,
            self::LICENSE => false,
            self::CERTIFICATE_OF_INCORPORATION => true,
            self::TAX_PIN => true,
            self::CR12_CR13 => true,
        };
    }
}
