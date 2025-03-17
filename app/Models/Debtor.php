<?php

namespace App\Models;

use App\Traits\DebtorCreditScore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Debtor extends Model
{
    use HasFactory, SoftDeletes, DebtorCreditScore;

    protected $fillable = [
        'name',
        'kra_pin',
        'email',
        'status',
        'status_notes',
        'status_updated_by',
        'status_updated_at',
        'listing_goes_live_at',
        'listed_at',
        'verification_token',
    ];

    protected $casts = [
        'listing_goes_live_at' => 'datetime',
        'listed_at' => 'datetime',
        'status_updated_at' => 'datetime',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(DebtorDocument::class);
    }

    public function getAmountOwedAttribute()
    {
        $businessId = Auth::user()->businesses()->first()?->id;
        if (!$businessId) {
            return 0;
        }

        $pivot = $this->businesses()->where('business_id', $businessId)->first()?->pivot;
        return $pivot ? $pivot->amount_owed : 0;
    }

    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'business_debtor')
            ->withPivot('amount_owed')
            ->withTimestamps();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class);
    }

    public function statusUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'status_updated_by');
    }

    public function asBusiness()
    {
        return Business::where('registration_number', $this->kra_pin)->first();
    }

    public function isBusiness(): bool
    {
        return Business::where('registration_number', $this->kra_pin)->exists();
    }

    public function generateVerificationToken(): string
    {
        $token = Str::random(64);
        $this->verification_token = $token;
        $this->save();

        return $token;
    }

    public function validateToken(string $token): bool
    {
        return $this->verification_token === $token;
    }

    public function getTotalAmountOwed(): float
    {
        return $this->businesses()->sum('amount_owed');
    }

    public function getAmountOwedToBusiness(Business $business): float
    {
        $relation = $this->businesses()->where('business_id', $business->id)->first();
        return $relation ? $relation->pivot->amount_owed : 0;
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
