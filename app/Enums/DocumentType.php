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
    case INVOICE = 'invoice';
    case PAYMENT_PROOF = 'payment_proof';
    case CONTRACT = 'contract';
    case DISPUTE_CLAIM = 'dispute_claim';
    case DISPUTE_RESPONSE = 'dispute_response';
    case EVIDENCE = 'evidence';
    case DEMAND_LETTER = 'demand_letter';
    case DELIVERY_NOTE = 'delivery_note';
    case PURCHASE_ORDER = 'purchase_order';
    case RECEIPT = 'receipt';

    public function label(): string
    {
        return match($this) {
            self::REGISTRATION => 'Registration Document',
            self::TAX => 'Tax Document',
            self::LICENSE => 'Business License',
            self::CERTIFICATE_OF_INCORPORATION => 'Certificate of Incorporation',
            self::TAX_PIN => 'Tax PIN Document',
            self::CR12_CR13 => 'CR12 or CR13 Document',
            self::INVOICE => 'Invoice',
            self::PAYMENT_PROOF => 'Payment Proof',
            self::CONTRACT => 'Contract or Agreement',
            self::DISPUTE_CLAIM => 'Dispute Claim',
            self::DISPUTE_RESPONSE => 'Dispute Response',
            self::EVIDENCE => 'Supporting Evidence',
            self::DEMAND_LETTER => 'Demand Letter',
            self::DELIVERY_NOTE => 'Delivery Note',
            self::PURCHASE_ORDER => 'Purchase Order',
            self::RECEIPT => 'Receipt',
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
            self::INVOICE => 'Invoice document showing amount owed',
            self::PAYMENT_PROOF => 'Proof of payment such as bank statement or transaction receipt',
            self::CONTRACT => 'Business contract or agreement document',
            self::DISPUTE_CLAIM => 'Document detailing dispute claims',
            self::DISPUTE_RESPONSE => 'Response to dispute claims',
            self::EVIDENCE => 'Any additional evidence supporting your case',
            self::DEMAND_LETTER => 'Legal demand letter for payment',
            self::DELIVERY_NOTE => 'Proof of delivery or services rendered',
            self::PURCHASE_ORDER => 'Official purchase order document',
            self::RECEIPT => 'Receipt for goods or services',
        };
    }

    public function isRequired(): bool
    {
        return match($this) {
            // Business registration documents
            self::REGISTRATION => true,
            self::TAX => true,
            self::LICENSE => false,
            self::CERTIFICATE_OF_INCORPORATION => true,
            self::TAX_PIN => true,
            self::CR12_CR13 => true,

            // Debt-related documents
            self::INVOICE => true,
            self::CONTRACT => false,
            self::PURCHASE_ORDER => false,
            self::DELIVERY_NOTE => false,

            // Payment-related documents
            self::PAYMENT_PROOF => false,
            self::RECEIPT => false,

            // Dispute-related documents
            self::DISPUTE_CLAIM => false,
            self::DISPUTE_RESPONSE => false,
            self::EVIDENCE => false,
            self::DEMAND_LETTER => false,
        };
    }

    /**
     * Get document types related to debt submission
     *
     * @return array
     */
    public static function getDebtDocumentTypes(): array
    {
        return [
            self::INVOICE,
            self::CONTRACT,
            self::PURCHASE_ORDER,
            self::DELIVERY_NOTE,
            self::DEMAND_LETTER,
            self::EVIDENCE
        ];
    }

    /**
     * Get document types related to dispute
     *
     * @return array
     */
    public static function getDisputeDocumentTypes(): array
    {
        return [
            self::DISPUTE_CLAIM,
            self::DISPUTE_RESPONSE,
            self::EVIDENCE,
            self::CONTRACT
        ];
    }

    /**
     * Get document types related to payment
     *
     * @return array
     */
    public static function getPaymentDocumentTypes(): array
    {
        return [
            self::PAYMENT_PROOF,
            self::RECEIPT
        ];
    }

    /**
     * Get document types related to business registration
     *
     * @return array
     */
    public static function getBusinessDocumentTypes(): array
    {
        return [
            self::REGISTRATION,
            self::TAX,
            self::LICENSE,
            self::CERTIFICATE_OF_INCORPORATION,
            self::TAX_PIN,
            self::CR12_CR13
        ];
    }
}
