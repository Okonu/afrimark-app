<?php

namespace App\Models;

use App\Enums\DebtorStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Debtor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
        'phone',
        'amount',
        'amount_paid',
        'status',
        'address',
        'notes',
        'due_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'due_date' => 'date',
        'status' => DebtorStatus::class,
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

}
