<?php

namespace App\Http\Controllers\V1;

use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\Notify;
use Illuminate\Validation\Rule;
use App\Notifications\Test\Like;
use App\Notifications\Test\Follow;
use App\Notifications\Test\Comment;
use App\Http\Controllers\Controller;
use Illuminate\Notifications\Notifiable;
use App\Http\Resources\NotificationResource;

class SendNotificationController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @return NotificationResource
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'exists:App\Models\User,id'],
            'actor_id' => ['sometimes', 'required', 'exists:App\Models\User,id'],
            'type' => ['required', Rule::in(['comment', 'like', 'follow'])]
        ]);

        $user = User::find($request->get('user_id'));

        if ($request->has('actor_id')) {
            $actor = User::find($request->get('actor_id'));
        } else {
            $actor = User::inRandomOrder()->where('id', '!=', $user->id)->first();
        }

        switch ($request->get('type')) {
            case 'like':
                $user->notify(new Like($actor));
                break;

            case 'comment':
                $user->notify(new Comment($actor));
                break;

            case 'follow':
                $user->notify(new Follow($actor));
                break;
        }

        return response()->json(['message' => 'Notification sent'], 201);
    }
}
