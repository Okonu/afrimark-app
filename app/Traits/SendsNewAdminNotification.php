<?php

namespace App\Traits;

use Filament\Notifications\Notification;
use App\Notifications\NewAdminAccountNotification;

trait SendsNewAdminNotification
{
    public function sendNewAdminNotification(string $panelId = 'admin'): bool
    {
        try {
            $this->notify(new NewAdminAccountNotification());

            Notification::make()
                ->title('Admin account created')
                ->body('An email has been sent with setup instructions.')
                ->success()
                ->send();

            \Log::info('New admin notification sent', [
                'email' => $this->email,
                'admin_id' => $this->id ?? null
            ]);

            return true;

        } catch (\Exception $e) {
            \Log::error('Exception while sending new admin notification', [
                'email' => $this->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->title('Error creating admin account')
                ->body('There was a problem sending the setup instructions.')
                ->danger()
                ->send();

            return false;
        }
    }
}
