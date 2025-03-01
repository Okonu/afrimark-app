<?php

namespace App\Models;

use App\Traits\HasDocuments;
use App\Traits\SendsUserNotifications;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

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

    public function debtors(): HasMany
    {
        return $this->hasMany(Debtor::class);
    }

    public function debtorsToOthers()
    {
        return Debtor::where('kra_pin', $this->registration_number);
    }
}
