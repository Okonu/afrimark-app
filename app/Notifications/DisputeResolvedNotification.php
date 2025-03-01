<?php

namespace App\Notifications;

use App\Models\Dispute;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DisputeResolvedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $dispute;

    /**
     * Create a new notification instance.
     */
    public function __construct(Dispute $dispute)
    {
        $this->dispute = $dispute;
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
        $debtor = $this->dispute->debtor;
        $message = (new MailMessage)
            ->subject('Update on Your Dispute')
            ->line('We have an update on the dispute you filed against the following debtor listing:')
            ->line('Business: ' . $debtor->business->name)
            ->line('Amount: ' . number_format($debtor->amount_owed, 2) . ' KES')
            ->line('Invoice: ' . ($debtor->invoice_number ?? 'N/A'));

        if ($this->dispute->status === 'resolved_approved') {
            $message->line('Your dispute has been approved and the listing has been removed.')
                ->line('Notes: ' . ($this->dispute->notes ?? 'No additional notes provided.'));
        } elseif ($this->dispute->status === 'resolved_rejected') {
            $message->line('Your dispute has been rejected and the listing will remain active.')
                ->line('Notes: ' . ($this->dispute->notes ?? 'No additional notes provided.'));
        } elseif ($this->dispute->status === 'under_review') {
            $message->line('The lister has requested additional information regarding your dispute:')
                ->line($this->dispute->notes)
                ->action('Provide Additional Information', route('filament.client.resources.disputes.view', ['record' => $this->dispute->id]));
        }

        return $message->line('If you have any questions, please log in to your account to view the full details.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $statusText = match($this->dispute->status) {
            'resolved_approved' => 'approved',
            'resolved_rejected' => 'rejected',
            'under_review' => 'needs more information',
            default => $this->dispute->status,
        };

        return [
            'dispute_id' => $this->dispute->id,
            'debtor_id' => $this->dispute->debtor_id,
            'status' => $this->dispute->status,
            'message' => "Your dispute has been {$statusText}",
            'notes' => $this->dispute->notes,
        ];
    }
}
