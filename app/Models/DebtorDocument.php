<?php

namespace App\Models;

use App\Enums\DocumentType;
use App\Traits\DocumentProcessable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DebtorDocument extends Model
{
    use HasFactory, SoftDeletes, DocumentProcessable;

    protected $fillable = [
        'debtor_id',
        'type',
        'file_path',
        'related_invoice_id',
        'original_filename',
        'uploaded_by',
        'processing_status',
        'processing_result',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'processing_result' => 'array',
    ];

    public function debtor(): BelongsTo
    {
        return $this->belongsTo(Debtor::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Extract invoice information from processed document
     *
     * @return array|null
     */
    public function extractInvoiceInformation(): ?array
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
                'invoice_number' => $extractedData['invoice_number'] ?? $extractedData['number'] ?? null,
                'invoice_date' => $extractedData['invoice_date'] ?? $extractedData['date'] ?? null,
                'due_date' => $extractedData['due_date'] ?? null,
                'amount' => $extractedData['total_amount'] ?? $extractedData['amount'] ?? $extractedData['total'] ?? null,
                'currency' => $extractedData['currency'] ?? 'KES',
                'payment_terms' => $extractedData['payment_terms'] ?? $extractedData['terms'] ?? null,
                'vendor' => $extractedData['vendor'] ?? $extractedData['supplier'] ?? $extractedData['from'] ?? null,
                'customer' => $extractedData['customer'] ?? $extractedData['client'] ?? $extractedData['to'] ?? null,
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
