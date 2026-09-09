<?php

namespace App\Notifications;

use App\Mail\SystemNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserResetPassword extends Notification implements ShouldQueue
{
    use Queueable;

    public $token;

    public function __construct($token)
    {
        info('token . ' . $token);
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new SystemNotification(
            subjectLine: '🔐 Reset Your Password',
            viewName: 'password-reset-request',
            data: [
                'resetUrl' => $resetUrl,
                'user' => $notifiable,
            ],
            user: $notifiable
        ))->to($notifiable->getEmailForPasswordReset());
    }
}
