<?php

namespace App\Notifications;

use App\Models\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BusinessVerificationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $business;
    protected $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(Business $business, string $token)
    {
        $this->business = $business;
        $this->token = $token;
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
        return (new MailMessage)
            ->subject('Verify Your Business Email Address')
            ->view('emails.business-verification', [
                'token' => $this->token,
                'business' => $this->business,
            ]);
    }
}
