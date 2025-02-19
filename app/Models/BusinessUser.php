<?php

namespace App\Models;

use App\Traits\SendsUserNotifications;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class BusinessUser extends Pivot
{
    use SoftDeletes, HasFactory, SendsUserNotifications, Notifiable;

    protected $table = 'business_users';

    public $incrementing = true;

    protected $fillable = ['user_id', 'business_id', 'role'];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
