<?php

namespace App\Notifications\Client;

use App\Traits\HasTokenGeneration;
use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class NewBusinessAccountNotification extends BaseResetPassword
{
    use HasTokenGeneration;

    protected $password;

    public function __construct(string $password)
    {
        $this->token = $this->createToken();
        $this->password = $password;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to Your Business Account')
            ->greeting("Hello {$notifiable->name}!")
            ->line('Your business account has been created successfully.')
            ->line('Here are your login credentials:')
            ->line("Email: {$notifiable->email}")
            ->line("Temporary Password: {$this->password}")
            ->action('Login to Your Account', url('/client/login'))
            ->line('Please change your password after your first login for security.')
            ->line('If you did not create this account, please contact our support team.');
    }
}
