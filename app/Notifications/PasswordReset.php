<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Lang;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\PasswordReset as PasswordResetMail;
use NotificationChannels\Twilio\TwilioSmsMessage;

class PasswordReset extends Notification implements ShouldQueue
{
    use Queueable;

    public $via;
    public $passwordReset;

    /**
     * Create a new notification instance.
     *
     * @param  array $via
     * @return void
     */
    public function __construct(array $via = ['mail'])
    {
        $this->via = $via;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return $this->via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new PasswordResetMail($notifiable->passwordReset))->to($notifiable->email);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return TwilioSmsMessage
     */
    public function toTwilio($notifiable)
    {
        $content = Lang::get(
            'Your password reset code is :code',
            ['code' => $notifiable->passwordReset->token]
        );

        return new TwilioSmsMessage($content);
    }
}
