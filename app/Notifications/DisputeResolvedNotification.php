<?php

namespace App\Notifications;

use App\Models\Dispute;
use App\Traits\QueuedNotifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class DisputeResolvedNotification extends Notification implements ShouldQueue
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
        } catch (\Exception $e) {
            Log::error("Error in DisputeResolvedNotification: " . $e->getMessage());

            // Fallback message
            return (new MailMessage)
                ->subject('Update on Your Dispute')
                ->line('There has been an update to your dispute. Please log in to see details.')
                ->action('View Dispute', route('filament.client.resources.disputes.index'));
        }
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

    /**
     * Handle a failed notification.
     *
     * @param \Exception $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        Log::error("DisputeResolvedNotification failed: " . $exception->getMessage());
        Log::error($exception->getTraceAsString());
    }
}
