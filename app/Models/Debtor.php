<?php

namespace App\Models;

use App\Enums\DebtorStatus;
use App\Models\User;
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
        'status_notes',
        'status_updated_by',
        'status_updated_at',
        'listing_goes_live_at',
        'listed_at',
    ];

    protected $casts = [
        'amount_owed' => 'decimal:2',
        'listing_goes_live_at' => 'datetime',
        'listed_at' => 'datetime',
        'status' => DebtorStatus::class,
        'status_updated_at' => 'datetime',
    ];

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

    public function statusUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'status_updated_by');
    }

    public function hasAllRequiredDocuments(): bool
    {
        return $this->documents()->count() > 0;
    }

    public function isListed(): bool
    {
        return $this->status === DebtorStatus::ACTIVE && $this->listed_at !== null;
    }

    public function scopeActive($query)
    {
        return $query->where('status', DebtorStatus::ACTIVE);
    }

    public function scopeDisputed($query)
    {
        return $query->where('status', DebtorStatus::DISPUTED);
    }

    public function scopePending($query)
    {
        return $query->where('status', DebtorStatus::PENDING);
    }

    public function scopePaid($query)
    {
        return $query->where('status', DebtorStatus::PAID);
    }
}
