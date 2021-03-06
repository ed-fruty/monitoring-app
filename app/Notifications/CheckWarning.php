<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CheckWarning extends \Spatie\ServerMonitor\Notifications\Notifications\CheckWarning
{
    /**
     * @return string
     */
    public function getSubject(): string
    {
        return 'Warning in check ' . parent::getSubject(); // TODO: Change the autogenerated stub
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->error()
            ->subject($this->getSubject())
            ->line($this->getSubject())
            ->line($this->getMessageText());
    }
}
