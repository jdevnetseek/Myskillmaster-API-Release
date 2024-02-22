<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Lang;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
use Illuminate\Notifications\Messages\MailMessage;

class OneTimePassword extends Notification implements ShouldQueue
{
    use Queueable;

    public $viaEmail;

    protected $otp;

    /**
     * Create a new notification instance.
     *
     * @param  array $via
     * @return void
     */
    public function __construct($otp, bool $viaEmail = false)
    {
        $this->otp      = $otp;
        $this->viaEmail = $viaEmail;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return $this->viaEmail ? ['mail'] : [TwilioChannel::class];
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
            ->line($this->getMessageContent($notifiable));
    }

    /**
     * Send the notification via twilio channel.
     *
     * @param  mixed  $notifiable
     * @return TwilioSmsMessage
     */
    public function toTwilio($notifiable)
    {
        return (new TwilioSmsMessage($this->getMessageContent($notifiable)))
            ->from(config('twilio-notification-channel.from'));
    }

    /**
     * Generates the messages content to be sent to the specified channel.
     *
     * @param User $notifiable
     * @return string
     */
    protected function getMessageContent($notifiable) : string
    {
        return Lang::get(
            'Your one time password is :otp',
            ['otp' => $this->otp]
        );
    }
}
