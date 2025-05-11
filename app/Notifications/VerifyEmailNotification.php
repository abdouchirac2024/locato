<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    public $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Vérification de votre email')
                    ->line('Votre code de vérification est: ' . $this->code)
                    ->line('Ce code expirera dans 24 heures.');
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
