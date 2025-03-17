<?php

namespace App\Models;

use App\Traits\DocumentProcessable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DisputeDocument extends Model
{
    use HasFactory, SoftDeletes, DocumentProcessable;

    protected $fillable = [
        'dispute_id',
        'file_path',
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

    public function dispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Extract dispute evidence from the processed document
     *
     * @return array|null
     */
    public function extractDisputeEvidence(): ?array
    {
        if (!$this->isProcessingSuccessful()) {
            return null;
        }

        $extractedData = $this->getExtractedData();

        if (!$extractedData) {
            return null;
        }

        // For contract documents, we get text-based analysis
        if (is_string($extractedData)) {
            // Look for potential evidence in the text
            $hasPotentialEvidence = $this->checkForEvidenceKeywords($extractedData);

            return [
                'document_text' => $extractedData,
                'potential_evidence' => $hasPotentialEvidence,
                'summary' => substr($extractedData, 0, 250) . (strlen($extractedData) > 250 ? '...' : ''),
            ];
        }

        // If we have structured data, extract relevant fields
        if (is_array($extractedData)) {
            return [
                'document_type' => $extractedData['document_type'] ?? null,
                'date' => $extractedData['date'] ?? null,
                'parties' => $extractedData['parties'] ?? null,
                'potential_evidence' => true,
                'summary' => $extractedData['summary'] ?? null,
            ];
        }

        return null;
    }

    /**
     * Check if the document might contain evidence supporting a dispute
     *
     * @return bool|null
     */
    public function mightSupportDispute(): ?bool
    {
        if (!$this->isProcessingSuccessful()) {
            return null;
        }

        $extractedData = $this->getExtractedData();

        if (!$extractedData) {
            return null;
        }

        // If it's text, check for evidence keywords
        if (is_string($extractedData)) {
            return $this->checkForEvidenceKeywords($extractedData);
        }

        return null;
    }

    /**
     * Check text for evidence keywords
     *
     * @param string $text
     * @return bool
     */
    protected function checkForEvidenceKeywords(string $text): bool
    {
        $text = strtolower($text);

        // Keywords that might indicate evidence supporting a dispute
        $positiveKeywords = [
            'payment received', 'paid', 'receipt', 'confirmation',
            'transaction complete', 'settled', 'cleared',
            'proof of payment', 'payment confirmation'
        ];

        foreach ($positiveKeywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }
}
