<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Traits\DocumentProcessable;
use App\Traits\Verifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessDocument extends Model
{
    use HasFactory, SoftDeletes, Verifiable, DocumentProcessable;

    protected $fillable = [
        'business_id',
        'type',
        'file_path',
        'original_filename',
        'status',
        'verification_notes',
        'verified_at',
        'verified_by',
        'processing_status',
        'processing_result',
        'processed_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'processed_at' => 'datetime',
        'type' => DocumentType::class,
        'status' => DocumentStatus::class,
        'processing_result' => 'array',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Extract business information from processed document
     *
     * @return array|null
     */
    public function extractBusinessInformation(): ?array
    {
        if (!$this->isProcessingSuccessful()) {
            return null;
        }

        $extractedData = $this->getExtractedData();

        if (!$extractedData) {
            return null;
        }

        if (is_array($extractedData)) {
            return [
                'name' => $extractedData['company_name'] ?? $extractedData['business_name'] ?? $extractedData['name'] ?? null,
                'registration_number' => $extractedData['registration_number'] ?? $extractedData['reg_number'] ?? $extractedData['kra_pin'] ?? null,
                'email' => $extractedData['email'] ?? null,
                'phone' => $extractedData['phone'] ?? $extractedData['phone_number'] ?? null,
                'address' => $extractedData['address'] ?? null,
            ];
        }

        if (is_string($extractedData)) {
            return [
                'description' => $extractedData,
            ];
        }

        return null;
    }
}
