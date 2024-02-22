<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class Notify extends Notification implements ShouldQueue
{
    use Queueable;

    private $data;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [FcmChannel::class, 'database'];
    }

    /**
     * Get the fcm representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \NotificationChannels\Fcm\FcmMessage
     */
    public function toFcm($notifiable)
    {
        $data = $this->data->data;
        $title = $this->data->title;
        $description = $this->data->description;
        $image_url = $this->data->image_url;

        $fcmNotification = FcmNotification::create()
            ->setTitle($title)
            ->setBody($description)
            ->setImage($image_url);

        $data = [
            'type' => $this->data->type,
            'data' => $data
        ];

        return FcmMessage::create()
            ->setData($data)
            ->setNotification($fcmNotification);
    }

    /**
     * Get the fcm representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'data' => $this->data
        ];
    }

    /**
     * Send report if notification failed;
     */
    public function failed($e)
    {
        report($e);
    }
}
