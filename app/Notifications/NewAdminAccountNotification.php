<?php

namespace App\Notifications;

use App\Traits\HasTokenGeneration;
use App\Traits\QueuedNotifications;
use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class NewAdminAccountNotification extends BaseResetPassword implements ShouldQueue
{
    use HasTokenGeneration, QueuedNotifications;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        $this->token = $this->createToken();
        $this->onQueue('notifications');
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        try {
            return (new MailMessage)
                ->subject('Welcome to Admin Panel')
                ->greeting('Hello!')
                ->line('You have been granted admin access to our system.')
                ->line('Please set up your password to get started.')
                ->action('Set Password', url('/admin/password-reset/request'))
                ->line('If you did not expect to receive this invitation, please ignore this email.');
        } catch (\Exception $e) {
            Log::error("Error in NewAdminAccountNotification: " . $e->getMessage());

            // Fallback message
            return (new MailMessage)
                ->subject('Welcome to Admin Panel')
                ->line('You have been granted admin access.')
                ->action('Set Password', url('/admin/password-reset/request'));
        }
    }

    /**
     * Handle a failed notification.
     *
     * @param \Exception $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        Log::error("NewAdminAccountNotification failed: " . $exception->getMessage());
        Log::error($exception->getTraceAsString());
    }
}
