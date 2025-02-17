<?php

namespace App\Traits;

use App\Enums\DocumentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait Verifiable
{
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isPending(): bool
    {
        return $this->status === DocumentStatus::PENDING;
    }

    public function isVerified(): bool
    {
        return $this->status === DocumentStatus::VERIFIED;
    }

    public function isRejected(): bool
    {
        return $this->status === DocumentStatus::REJECTED;
    }

    public function markAsVerified(User $verifier, ?string $notes = null): void
    {
        $this->update([
            'status' => DocumentStatus::VERIFIED,
            'verification_notes' => $notes,
            'verified_at' => now(),
            'verified_by' => $verifier->id,
        ]);
    }

    public function markAsRejected(User $verifier, string $notes): void
    {
        $this->update([
            'status' => DocumentStatus::REJECTED,
            'verification_notes' => $notes,
            'verified_at' => now(),
            'verified_by' => $verifier->id,
        ]);
    }
}
