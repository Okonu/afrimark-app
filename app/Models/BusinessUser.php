<?php

namespace App\Models;

use App\Traits\SendsUserNotifications;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class BusinessUser extends Model
{
    use SoftDeletes, HasFactory, SendsUserNotifications, Notifiable;

    protected $fillable = ['user_id', 'business_id', 'role'];
}
