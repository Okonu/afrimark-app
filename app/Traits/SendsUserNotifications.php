<?php

namespace App\Traits;

use App\Notifications\NewAdminAccountNotification;
use Illuminate\Support\Facades\Password;
use Filament\Notifications\Notification;
use App\Notifications\AdminResetPasswordNotification;
use App\Notifications\Client\ClientResetPasswordNotification;
use App\Notifications\Client\NewBusinessAccountNotification;
use App\Notifications\Client\BusinessUserAccountNotification;

trait SendsUserNotifications
{
    use SendsFilamentPasswordResetLinks;

    public function sendNewAdminNotification(): bool
    {
        try {
            $this->notify(new NewAdminAccountNotification());

            Notification::make()
                ->title('Admin account created')
                ->success()
                ->send();

            return true;
        } catch (\Exception $e) {
            \Log::error('Error sending admin notification', [
                'email' => $this->email,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    public function sendNewBusinessNotification(string $password): bool
    {
        try {
            $this->notify(new NewBusinessAccountNotification($password));

            Notification::make()
                ->title('Business account created')
                ->success()
                ->send();

            return true;
        } catch (\Exception $e) {
            \Log::error('Error sending business notification', [
                'email' => $this->email,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    public function sendBusinessUserNotification(): bool
    {
        try {
            $this->notify(new BusinessUserAccountNotification());

            Notification::make()
                ->title('Business user account created')
                ->success()
                ->send();

            return true;
        } catch (\Exception $e) {
            \Log::error('Error sending business user notification', [
                'email' => $this->email,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}
