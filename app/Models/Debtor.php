<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Traits\Verifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Debtor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'kra_pin',
        'name',
        'email',
        'amount_owed',
        'invoice_number',
        'status',
        'listing_goes_live_at',
        'listed_at',
    ];

    protected $casts = [
        'amount_owed' => 'decimal:2',
        'listing_goes_live_at' => 'datetime',
        'listed_at' => 'datetime',
    ];

    // Possible statuses: pending, disputed, active, paid

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function documents()
    {
        return $this->hasMany(DebtorDocument::class);
    }

    public function disputes()
    {
        return $this->hasMany(Dispute::class);
    }

    public function hasAllRequiredDocuments(): bool
    {
        // Check if all required supporting documents are uploaded
        return $this->documents()->count() > 0;
    }

    public function isListed(): bool
    {
        return $this->status === 'active' && $this->listed_at !== null;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDisputed($query)
    {
        return $query->where('status', 'disputed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}
