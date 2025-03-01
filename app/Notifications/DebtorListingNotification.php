<?php

namespace App\Notifications;

use App\Models\Debtor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DebtorListingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $debtor;

    /**
     * Create a new notification instance.
     */
    public function __construct(Debtor $debtor)
    {
        $this->debtor = $debtor;
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
            ->subject('Action Required: Your Business Has Been Listed as a Debtor')
            ->view('emails.debtor-listing', [
                'debtor' => $this->debtor
            ]);
    }
}
