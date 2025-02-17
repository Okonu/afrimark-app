<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Traits\Verifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessDocument extends Model
{
    use HasFactory, SoftDeletes, Verifiable;

    protected $fillable = [
        'business_id',
        'type',
        'file_path',
        'original_filename',
        'status',
        'verification_notes',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'type' => DocumentType::class,
        'status' => DocumentStatus::class,
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
