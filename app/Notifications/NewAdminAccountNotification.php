<?php

namespace App\Notifications;

use App\Traits\HasTokenGeneration;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;

class NewAdminAccountNotification extends BaseResetPassword
{
    use HasTokenGeneration;

    public function __construct()
    {
        $this->token = $this->createToken();
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to Admin Panel')
            ->greeting('Hello!')
            ->line('You have been granted admin access to our system.')
            ->line('Please set up your password to get started.')
            ->action('Set Password', url('/admin/password-reset/request'))
            ->line('If you did not expect to receive this invitation, please ignore this email.');
    }
}
