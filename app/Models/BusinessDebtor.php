<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BusinessDebtor extends Pivot
{
    public $incrementing = true;

    protected $fillable = [
        'business_id',
        'debtor_id',
        'amount_owed',
        'average_payment_terms',
        'median_payment_terms',
        'average_days_overdue',
        'median_days_overdue',
        'average_dbt_ratio',
        'median_dbt_ratio'
    ];

    protected $casts = [
        'amount_owed' => 'decimal:2',
        'average_payment_terms' => 'decimal:2',
        'median_payment_terms' => 'decimal:2',
        'average_days_overdue' => 'decimal:2',
        'median_days_overdue' => 'decimal:2',
        'average_dbt_ratio' => 'decimal:4',
        'median_dbt_ratio' => 'decimal:4',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function debtor(): BelongsTo
    {
        return $this->belongsTo(Debtor::class);
    }

    public function invoices(): HasMany
    {
        return Invoice::where('business_id', $this->business_id)
            ->where('debtor_id', $this->debtor_id)
            ->get();
    }
}
