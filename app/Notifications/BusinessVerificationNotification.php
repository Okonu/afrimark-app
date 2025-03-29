<?php

namespace App\Notifications;

use App\Models\Business;
use App\Traits\QueuedNotifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class BusinessVerificationNotification extends Notification implements ShouldQueue
{
    use QueuedNotifications;

    protected $business;
    protected $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(Business $business, string $token)
    {
        $this->business = $business;
        $this->token = $token;
        $this->onQueue('notifications');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        try {
            return (new MailMessage)
                ->subject('Verify Your Business Email Address')
                ->view('emails.business-verification', [
                    'token' => $this->token,
                    'business' => $this->business,
                ]);
        } catch (\Exception $e) {
            Log::error("Error in BusinessVerificationNotification: " . $e->getMessage());

            return (new MailMessage)
                ->subject('Verify Your Business Email Address')
                ->line('Please verify your business email address.')
                ->action('Verify Email', route('business.verify', ['token' => $this->token]))
                ->line('If you did not create an account, no further action is required.');
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
        Log::error("BusinessVerificationNotification failed: " . $exception->getMessage());
        Log::error($exception->getTraceAsString());
    }
}
