<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class CheckRestored extends \Spatie\ServerMonitor\Notifications\Notifications\CheckRestored
{
    /**
     * @return string
     */
    public function getSubject(): string
    {
        return 'Restored check ' . parent::getSubject();
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
            ->success()
            ->subject($this->getSubject())
            ->line($this->getSubject())
            ->line($this->getMessageText());
    }
}
