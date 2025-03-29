<?php

namespace App\Notifications\Client;

use App\Traits\HasTokenGeneration;
use App\Traits\QueuedNotifications;
use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class BusinessUserAccountNotification extends BaseResetPassword implements ShouldQueue
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
                ->subject('Welcome to Business Portal')
                ->greeting("Hello {$notifiable->name}!")
                ->line('You have been added as a user to a business account.')
                ->line('Please set up your password to access your account.')
                ->action('Set Password', url('/client/password-reset/request'))
                ->line('If you did not expect this invitation, please ignore this email.');
        } catch (\Exception $e) {
            Log::error("Error in BusinessUserAccountNotification: " . $e->getMessage());

            // Fallback message
            return (new MailMessage)
                ->subject('Welcome to Business Portal')
                ->line('You have been added as a user to a business account.')
                ->action('Set Password', url('/client/password-reset/request'));
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
        Log::error("BusinessUserAccountNotification failed: " . $exception->getMessage());
        Log::error($exception->getTraceAsString());
    }
}
