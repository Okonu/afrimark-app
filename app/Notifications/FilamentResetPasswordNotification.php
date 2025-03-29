<?php

namespace App\Notifications;

use App\Traits\QueuedNotifications;
use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class FilamentResetPasswordNotification extends BaseResetPassword implements ShouldQueue
{
    use QueuedNotifications;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        parent::__construct($this->createToken());
        $this->onQueue('notifications');
    }

    /**
     * Create a token for the user.
     *
     * @return string
     */
    protected function createToken()
    {
        return \Illuminate\Support\Str::random(64);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        try {
            return (new MailMessage)
                ->subject('Welcome - Set Your Password')
                ->line('An account has been created for you.')
                ->action('Set Password', url('/admin/password-reset/request'))
                ->line('Click the button above to set your password.')
                ->line('If you did not request this account, no further action is required.');
        } catch (\Exception $e) {
            Log::error("Error in FilamentResetPasswordNotification: " . $e->getMessage());

            // Fallback message
            return (new MailMessage)
                ->subject('Set Your Password')
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
        Log::error("FilamentResetPasswordNotification failed: " . $exception->getMessage());
        Log::error($exception->getTraceAsString());
    }
}
