<?php

namespace App\Models;

use App\Traits\SendsUserNotifications;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Filament\Forms;
use App\Traits\SendsNewAdminNotification;

class Admin extends Model
{
    use SoftDeletes, HasFactory, SendsUserNotifications, Notifiable;

    protected $fillable = ['user_id', 'is_active'];

    protected static function booted()
    {
        static::created(function ($admin) {
            $admin->email = $admin->user->email;
            $admin->sendNewAdminNotification();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Route notifications for the mail channel.
     */
    public function routeNotificationForMail(): string
    {
        return $this->user->email;
    }

    public static function getForm(): array
    {
        return [
            Forms\Components\Select::make('user_id')
                ->createOptionForm(User::getForm())
                ->searchable()
                ->relationship('user', 'name')
                ->required()
                ->afterStateUpdated(function ($state, $set) {
                    if (!Admin::where('user_id', $state)->exists()) {
                        $set('is_active', true);
                    }
                }),
            Forms\Components\Toggle::make('is_active')
                ->required(),
        ];
    }
}
