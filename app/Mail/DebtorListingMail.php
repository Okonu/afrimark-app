<?php

namespace App\Mail;

use App\Models\Debtor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DebtorListingMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $debtor;

    /**
     * Create a new message instance.
     */
    public function __construct(Debtor $debtor)
    {
        $this->debtor = $debtor;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Important: Your Business Has Been Listed as a Debtor',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.debtor-listing',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.debtor-listing')
            ->with([
                'debtor' => $this->debtor,
            ]);
    }
}
