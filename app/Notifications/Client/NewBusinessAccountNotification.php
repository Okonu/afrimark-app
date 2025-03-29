<?php

namespace App\Notifications\Client;

use App\Traits\HasTokenGeneration;
use App\Traits\QueuedNotifications;
use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class NewBusinessAccountNotification extends BaseResetPassword implements ShouldQueue
{
    use HasTokenGeneration, QueuedNotifications;

    protected $password;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $password)
    {
        $this->token = $this->createToken();
        $this->password = $password;
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
                ->subject('Welcome to Your Business Account')
                ->greeting("Hello {$notifiable->name}!")
                ->line('Your business account has been created successfully.')
                ->line('Here are your login credentials:')
                ->line("Email: {$notifiable->email}")
                ->line("Temporary Password: {$this->password}")
                ->action('Login to Your Account', url('/client/login'))
                ->line('Please change your password after your first login for security.')
                ->line('If you did not create this account, please contact our support team.');
        } catch (\Exception $e) {
            Log::error("Error in NewBusinessAccountNotification: " . $e->getMessage());

            // Fallback message
            return (new MailMessage)
                ->subject('Welcome to Your Business Account')
                ->line('Your business account has been created with a temporary password.')
                ->line("Email: {$notifiable->email}")
                ->line("Temporary Password: {$this->password}")
                ->action('Login to Your Account', url('/client/login'));
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
        Log::error("NewBusinessAccountNotification failed: " . $exception->getMessage());
        Log::error($exception->getTraceAsString());
    }
}
