<?php

namespace App\Traits;

use App\Notifications\Client\ClientResetPasswordNotification;
use Illuminate\Support\Facades\Password;
use Filament\Notifications\Notification;
use App\Notifications\FilamentResetPasswordNotification;

trait SendsFilamentPasswordResetLinks
{
    public function sendPasswordResetLink(string $panelId = 'admin'): bool
    {
        try {
            $notificationClass = $panelId === 'admin'
                ? FilamentResetPasswordNotification::class
                : ClientResetPasswordNotification::class;

            $this->notify(new $notificationClass());

            Notification::make()
                ->title('Password reset instructions sent')
                ->success()
                ->send();

            \Log::info('Password reset notification sent', [
                'email' => $this->email,
                'panel' => $panelId
            ]);

            return true;

        } catch (\Exception $e) {
            \Log::error('Exception while sending password reset link', [
                'email' => $this->email,
                'panel' => $panelId,
                'error' => $e->getMessage()
            ]);

            Notification::make()
                ->title('Error sending password reset instructions')
                ->danger()
                ->send();

            return false;
        }
    }
}
