<?php

namespace App\Notifications;

use App\Models\Debtor;
use App\Traits\QueuedNotifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class DebtorListingNotification extends Notification implements ShouldQueue
{
    use QueuedNotifications;

    protected $debtor;

    /**
     * Create a new notification instance.
     */
    public function __construct(Debtor $debtor)
    {
        $this->debtor = $debtor;
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
            $businessName = $this->debtor->business ? $this->debtor->business->name : 'A business on our platform';

            $registrationUrl = route('debtor.verify', [
                'debtor_id' => $this->debtor->id,
                'token' => $this->debtor->verification_token
            ]);

            $loginUrl = route('filament.client.auth.login', [
                'redirect' => route('filament.client.pages.disputes-page-manager', ['tab' => 'disputable-listings'])
            ]);

            return (new MailMessage)
                ->subject('Action Required: Your Business Has Been Listed as a Debtor')
                ->view('emails.debtor-listing', [
                    'debtor' => $this->debtor,
                    'businessName' => $businessName,
                    'amountOwed' => number_format($this->debtor->amount_owed, 2),
                    'invoiceNumber' => $this->debtor->invoice_number ?? 'N/A',
                    'disputeUrl' => $loginUrl,
                    'registrationUrl' => $registrationUrl,
                    'appName' => config('app.name')
                ]);
        } catch (\Exception $e) {
            Log::error("Error generating debtor notification email: " . $e->getMessage());

            return (new MailMessage)
                ->subject('Important: Your Business Has Been Listed as a Debtor')
                ->line("Your business has been listed as a debtor for {$this->debtor->amount_owed} KES.")
                ->line("This listing will become publicly visible in 7 days unless resolved.")
                ->line("Please login to our system to address this matter.");
        }
    }
}
