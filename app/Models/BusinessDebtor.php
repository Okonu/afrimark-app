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
        'amount_owed'
    ];

    protected $casts = [
        'amount_owed' => 'decimal:2',
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
