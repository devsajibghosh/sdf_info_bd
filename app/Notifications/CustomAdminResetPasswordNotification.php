<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomAdminResetPasswordNotification extends Notification
{
    public function __construct(public string $token)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // $url = url(route('admin.password.set', [
        //     'token' => $this->token,
        //     'email' => $notifiable->getEmailForPasswordReset()
        // ], false));

        $url = route('admin.password.set', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset()
        ]);

        return (new MailMessage)
            ->subject('Reset Your Admin Password')
            ->line('Click the button below to reset your password.')
            ->action('Reset Password', $url)
            ->line('If you did not request a password reset, no further action is required.');
    }
}
