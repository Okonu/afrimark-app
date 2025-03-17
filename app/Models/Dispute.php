<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dispute extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'debtor_id',
        'business_id',
        'dispute_type',
        'description',
        'status',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    // Possible statuses: pending, under_review, resolved_approved, resolved_rejected

    public function debtor(): BelongsTo
    {
        return $this->belongsTo(Debtor::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DisputeDocument::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function isResolved(): bool
    {
        return in_array($this->status, ['resolved_approved', 'resolved_rejected']);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isUnderReview(): bool
    {
        return $this->status === 'under_review';
    }

    public function getProcessedDocuments()
    {
        return $this->documents()
            ->whereNotNull('processed_at')
            ->where('processing_status', 'completed')
            ->get();
    }

    public function hasSupportingDocuments(): bool
    {
        $processedDocs = $this->getProcessedDocuments();

        foreach ($processedDocs as $document) {
            if ($document->mightSupportDispute()) {
                return true;
            }
        }

        return false;
    }

    public function getDocumentsSummary(): string
    {
        $processedDocs = $this->getProcessedDocuments();

        if ($processedDocs->isEmpty()) {
            return "No processed documents available.";
        }

        $summaries = [];
        foreach ($processedDocs as $document) {
            $evidence = $document->extractDisputeEvidence();
            if ($evidence && isset($evidence['summary'])) {
                $summaries[] = $evidence['summary'];
            }
        }

        if (empty($summaries)) {
            return "No document summaries available.";
        }

        return implode("\n\n", $summaries);
    }
}
