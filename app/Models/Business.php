<?php

namespace App\Models;

use App\Traits\HasDocuments;
use App\Traits\SendsUserNotifications;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class Business extends Model
{
    use SoftDeletes, HasFactory, HasDocuments, Notifiable, SendsUserNotifications;
    protected $fillable = ['name', 'email', 'address', 'registration_number'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'business_users')
            ->using(BusinessUser::class)
            ->withTimestamps();
    }

    public function documents(): HasMany
    {
        return $this->hasMany(BusinessDocument::class);
    }

    public function debtors(): BelongsToMany
    {
        return $this->belongsToMany(Debtor::class, 'business_debtor')
            ->withPivot('amount_owed')
            ->withTimestamps();
    }

    public function debtorsToOthers(): Builder
    {
        return Debtor::where('kra_pin', $this->registration_number);
    }

    public function getTotalAmountOwedToOthers(): float
    {
        return DB::table('debtors')
            ->join('business_debtor', 'debtors.id', '=', 'business_debtor.debtor_id')
            ->where('debtors.kra_pin', $this->registration_number)
            ->whereNull('debtors.deleted_at')
            ->sum('business_debtor.amount_owed');
    }
}
