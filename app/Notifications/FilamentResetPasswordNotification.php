<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class FilamentResetPasswordNotification extends BaseResetPassword
{
    public function __construct()
    {
        parent::__construct($this->createToken());
    }

    protected function createToken()
    {
        return \Illuminate\Support\Str::random(64);
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome - Set Your Password')
            ->line('An account has been created for you.')
            ->action('Set Password', url('/admin/password-reset/request'))
            ->line('Click the button above to set your password.')
            ->line('If you did not request this account, no further action is required.');
    }
}
