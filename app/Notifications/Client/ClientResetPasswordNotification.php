<?php

namespace App\Notifications\Client;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use App\Traits\HasTokenGeneration;

class ClientResetPasswordNotification extends BaseResetPassword
{
    use HasTokenGeneration;

    public function __construct()
    {
        parent::__construct($this->createToken());
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reset Your Password')
            ->line('You are receiving this email because we received a password reset request for your business account.')
            ->action('Reset Password', url('/client/password-reset/request'))
            ->line('If you did not request a password reset, no further action is required.');
    }
}
