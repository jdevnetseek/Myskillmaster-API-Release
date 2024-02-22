<?php

namespace App\Notifications\Subscription;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class ChargeFailed extends Notification
{
    use Queueable;

    public $error;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($error = null)
    {
        $this->error = $error;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(config('app.name') . ' Payment Charge Notification')
            ->line('Your invoice payment has failed.')
            ->line(new HtmlString('Reason: <strong>' . $this->error['message'] . '</strong>'))
            ->line('Please log in to your account and update your payment information as soon as possible to avoid any interruption to your subscription.')
            ->line("If you have any questions or concerns, please don't hesitate to contact us.")
            ->line('Thank you for your attention to this matter.');
    }
}
