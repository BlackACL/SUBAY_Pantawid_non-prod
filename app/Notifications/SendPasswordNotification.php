<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendPasswordNotification extends Notification
{
    use Queueable;

    protected $password;

    public function __construct($password)
    {
        $this->password = $password;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Account Password')
            ->greeting('Hello ' . $notifiable->fullname . ',')
            ->line('Your account has been created. Here is your password:')
            ->line($this->password)
            ->line('Please log in and change your password as soon as possible.')
            ->salutation('Thank you!');
    }
}
