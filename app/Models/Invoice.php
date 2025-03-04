<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'due_amount' => 'decimal:2',
        'invoice_amount' => 'decimal:2',
    ];

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

    public function isOverdue(): bool
    {
        return now()->greaterThan($this->due_date);
    }

    public function daysUntilDue(): int
    {
        return now()->diffInDays($this->due_date, false);
    }
}
