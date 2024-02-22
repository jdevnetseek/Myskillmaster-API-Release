<?php

namespace App\Notifications\Test;

use App\Models\User;
use Illuminate\Bus\Queueable;
use App\Http\Resources\UserResource;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class Like extends Notification implements ShouldQueue
{
    use Queueable;

    public $type = 'like';
    public $actor;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $actor)
    {
        $this->actor = $actor->load('avatar');
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
        $data = $this->data($notifiable);

        $fcmNotification = FcmNotification::create()
            ->setTitle(config('app.name'))
            ->setBody($data['message']);

        return FcmMessage::create()
            ->setData([
                'payload' => json_encode($data)
            ])
            ->setNotification($fcmNotification);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return $this->data($notifiable);
    }

    private function data($notifiable)
    {
        return [
            'id'                => $this->id,
            'type'              => $this->type,
            'message'           => "{$this->actor->full_name} liked your post",
            'actor_id'          => $this->actor->id,
            'actor_name'        => $this->actor->full_name,
            'actor_avatar'      => optional($this->actor->avatar)->getFullUrl(),
            'notifiable_id'     => $notifiable->id,
            'notifiable_name'   => $notifiable->full_name,
            'notifiable_avatar' => optional($notifiable->avatar)->getFullUrl(),
            'post_id'           => null,
            'timestamp'         => now()->toISOString()
        ];
    }
}
