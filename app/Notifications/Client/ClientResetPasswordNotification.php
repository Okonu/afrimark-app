<?php

namespace App\Notifications\Client;

use App\Traits\HasTokenGeneration;
use App\Traits\QueuedNotifications;
use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class ClientResetPasswordNotification extends BaseResetPassword implements ShouldQueue
{
    use HasTokenGeneration, QueuedNotifications;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        parent::__construct($this->createToken());
        $this->onQueue('notifications');
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        try {
            return (new MailMessage)
                ->subject('Reset Your Password')
                ->line('You are receiving this email because we received a password reset request for your business account.')
                ->action('Reset Password', url('/client/password-reset/request'))
                ->line('If you did not request a password reset, no further action is required.');
        } catch (\Exception $e) {
            Log::error("Error in ClientResetPasswordNotification: " . $e->getMessage());

            // Fallback message
            return (new MailMessage)
                ->subject('Reset Your Password')
                ->line('You are receiving this email because we received a password reset request for your account.')
                ->action('Reset Password', url('/client/password-reset/request'));
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
        Log::error("ClientResetPasswordNotification failed: " . $exception->getMessage());
        Log::error($exception->getTraceAsString());
    }
}
