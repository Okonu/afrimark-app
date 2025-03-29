<?php

namespace App\Models;

use App\Services\Calculations\InvoiceCalculationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'debtor_id',
        'business_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'due_amount',
        'invoice_amount',
        'payment_terms',
        'days_overdue',
        'dbt_ratio'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'due_amount' => 'decimal:2',
        'invoice_amount' => 'decimal:2',
        'dbt_ratio' => 'decimal:4',
    ];

    /**
     * Recalculate metrics before saving the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            $invoice->recalculateMetrics();
        });

        static::updating(function ($invoice) {
            if ($invoice->isDirty(['invoice_date', 'due_date', 'payment_terms'])) {
                $invoice->recalculateMetrics();
            }
        });

        static::saved(function ($invoice) {
            if ($invoice->wasChanged(['due_amount', 'payment_terms', 'days_overdue', 'dbt_ratio'])) {
                app(InvoiceCalculationService::class)->updateBusinessDebtorMetrics(
                    $invoice->business_id,
                    $invoice->debtor_id
                );
            }
        });
    }

    /**
     * Recalculate all metrics based on current invoice data
     */
    public function recalculateMetrics()
    {
        $calculationService = app(InvoiceCalculationService::class);

        $metrics = $calculationService->calculateInvoiceMetrics(
            $this->invoice_date,
            $this->due_date
        );

        $this->payment_terms = $metrics['payment_terms'];
        $this->due_date = $metrics['due_date'];
        $this->days_overdue = $metrics['days_overdue'];
        $this->dbt_ratio = $metrics['dbt_ratio'];
    }

    public function debtor(): BelongsTo
    {
        return $this->belongsTo(Debtor::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function businessDebtor()
    {
        return BusinessDebtor::where('business_id', $this->business_id)
            ->where('debtor_id', $this->debtor_id)
            ->first();
    }

    /**
     * Get documents associated with this invoice
     * Documents are stored in the debtor_documents table with a reference to this invoice
     */
    public function documents(): HasMany
    {
        return $this->hasMany(DebtorDocument::class, 'related_invoice_id');
    }

    public function isOverdue(): bool
    {
        return now()->greaterThan($this->due_date);
    }

    public function daysUntilDue(): int
    {
        return now()->diffInDays($this->due_date, false);
    }
}
