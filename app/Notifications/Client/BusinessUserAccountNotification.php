<?php

namespace App\Notifications\Client;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use App\Traits\HasTokenGeneration;

class BusinessUserAccountNotification extends BaseResetPassword
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
            ->subject('Welcome to Business Portal')
            ->greeting("Hello {$notifiable->name}!")
            ->line('You have been added as a user to a business account.')
            ->line('Please set up your password to access your account.')
            ->action('Set Password', url('/client/password-reset/request'))
            ->line('If you did not expect this invitation, please ignore this email.');
    }
}
