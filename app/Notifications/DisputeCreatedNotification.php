<?php

namespace App\Notifications;

use App\Models\Dispute;
use App\Traits\QueuedNotifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class DisputeCreatedNotification extends Notification implements ShouldQueue
{
    use QueuedNotifications;

    protected $dispute;

    /**
     * Create a new notification instance.
     */
    public function __construct(Dispute $dispute)
    {
        $this->dispute = $dispute;
        $this->onQueue('notifications');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        try {
            $debtor = $this->dispute->debtor;
            $business = $debtor->business;

            return (new MailMessage)
                ->subject('Dispute Filed Against Your Debtor Listing')
                ->line('A dispute has been filed against one of your debtor listings.')
                ->line('Business: ' . $debtor->name)
                ->line('Amount: ' . number_format($debtor->amount_owed, 2) . ' KES')
                ->line('Invoice: ' . ($debtor->invoice_number ?? 'N/A'))
                ->line('Dispute Type: ' . $this->getDisputeTypeText($this->dispute->dispute_type))
                ->line('Description: ' . $this->dispute->description)
                ->action('Respond to Dispute', route('filament.client.resources.disputes.respond', ['record' => $this->dispute->id]))
                ->line('Please review and respond to this dispute promptly to maintain the accuracy of your credit listings.');
        } catch (\Exception $e) {
            Log::error("Error in DisputeCreatedNotification: " . $e->getMessage());

            return (new MailMessage)
                ->subject('Dispute Filed Against Your Debtor Listing')
                ->line('A dispute has been filed against one of your debtor listings.')
                ->action('Respond to Dispute', route('filament.client.resources.disputes.index'));
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $debtor = $this->dispute->debtor;

        return [
            'dispute_id' => $this->dispute->id,
            'debtor_id' => $debtor->id,
            'debtor_name' => $debtor->name,
            'amount_owed' => $debtor->amount_owed,
            'dispute_type' => $this->dispute->dispute_type,
            'message' => 'A dispute has been filed by ' . $debtor->name,
        ];
    }

    /**
     * Get the human-readable dispute type.
     *
     * @param string $type
     * @return string
     */
    protected function getDisputeTypeText(string $type): string
    {
        return match($type) {
            'wrong_amount' => 'Wrong Amount',
            'no_debt' => 'No Debt Exists',
            'already_paid' => 'Already Paid',
            'wrong_business' => 'Wrong Business Listed',
            'other' => 'Other',
            default => $type,
        };
    }

    /**
     * Handle a failed notification.
     *
     * @param \Exception $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        Log::error("DisputeCreatedNotification failed: " . $exception->getMessage());
        Log::error($exception->getTraceAsString());
    }
}
