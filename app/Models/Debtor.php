<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Debtor extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
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

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'listing_goes_live_at' => 'datetime',
        'listed_at' => 'datetime',
        'status_updated_at' => 'datetime',
    ];

    /**
     * Get businesses to which this debtor owes money.
     */
    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'business_debtor')
            ->withPivot('amount_owed')
            ->withTimestamps();
    }

    /**
     * Get all invoices related to this debtor.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get all disputes initiated by this debtor.
     */
    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class);
    }

    /**
     * Get the user who updated the debtor's status.
     */
    public function statusUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'status_updated_by');
    }

    /**
     * Get business with matching KRA PIN if this debtor is a business.
     */
    public function asBusiness()
    {
        return Business::where('registration_number', $this->kra_pin)->first();
    }

    /**
     * Check if the debtor is also a registered business.
     */
    public function isBusiness(): bool
    {
        return Business::where('registration_number', $this->kra_pin)->exists();
    }

    /**
     * Generate a verification token for the debtor.
     */
    public function generateVerificationToken(): string
    {
        $token = Str::random(64);
        $this->verification_token = $token;
        $this->save();

        return $token;
    }

    /**
     * Validate a token against the debtor's verification token.
     */
    public function validateToken(string $token): bool
    {
        return $this->verification_token === $token;
    }

    /**
     * Get the total amount owed by this debtor across all businesses.
     */
    public function getTotalAmountOwed(): float
    {
        return $this->businesses()->sum('amount_owed');
    }

    /**
     * Get amount owed to a specific business.
     */
    public function getAmountOwedToBusiness(Business $business): float
    {
        $relation = $this->businesses()->where('business_id', $business->id)->first();
        return $relation ? $relation->pivot->amount_owed : 0;
    }

    /**
     * Scope query to active debtors.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope query to disputed debtors.
     */
    public function scopeDisputed($query)
    {
        return $query->where('status', 'disputed');
    }

    /**
     * Scope query to pending debtors.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope query to paid debtors.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}
