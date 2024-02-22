<?php

namespace Database\Seeders;

use App\Models\User;
use Ramsey\Uuid\Uuid;
use App\Models\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use App\Notifications\Test\Like;
use App\Notifications\Test\Follow;
use App\Notifications\Test\Comment;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::with('avatar')->whereNotNull('full_name')->get();

        foreach ($users as $user) {
            if ($user->full_name) {
                $actor = $users->random();
                Notification::create([
                    'id' => $id = Uuid::uuid4(),
                    'type' => Like::class,
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->id,
                    'data' => [
                        'id' => $id,
                        'type' => 'like',
                        'message' => $actor->full_name . ' liked your post',
                        'actor_id' => $actor->id,
                        'actor_name' => $actor->full_name,
                        'actor_avatar' => optional($actor->avatar)->getFullUrl(),
                        'notifiable_id' => $user->id,
                        'notifiable_name' => $user->full_name,
                        'notifiable_avatar' => optional($user->avatar)->getFullUrl(),
                        'timestamp' => now()
                    ]
                ]);

                $actor = $users->random();
                Notification::create([
                    'id' => $id = Uuid::uuid4(),
                    'type' => Comment::class,
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->id,
                    'data' => [
                        'id' => $id,
                        'type' => 'comment',
                        'message' => $actor->full_name . ' commented on your post',
                        'actor_id' => $actor->id,
                        'actor_name' => $actor->full_name,
                        'actor_avatar' => optional($actor->avatar)->getFullUrl(),
                        'notifiable_id' => $user->id,
                        'notifiable_name' => $user->full_name,
                        'notifiable_avatar' => optional($user->avatar)->getFullUrl(),
                        'timestamp' => now()
                    ]
                ]);

                $actor = $users->random();
                Notification::create([
                    'id' => $id = Uuid::uuid4(),
                    'type' => Follow::class,
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->id,
                    'data' => [
                        'id' => $id,
                        'type' => 'follow',
                        'message' => $actor->full_name . ' started following you',
                        'actor_id' => $actor->id,
                        'actor_name' => $actor->full_name,
                        'actor_avatar' => optional($actor->avatar)->getFullUrl(),
                        'notifiable_id' => $user->id,
                        'notifiable_name' => $user->full_name,
                        'notifiable_avatar' => optional($user->avatar)->getFullUrl(),
                        'timestamp' => now()
                    ]
                ]);
            }
        }
    }
}
