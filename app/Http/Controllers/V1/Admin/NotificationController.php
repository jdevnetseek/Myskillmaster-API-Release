<?php

namespace App\Http\Controllers\V1\Admin;

use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Resources\NotificationResource;

class NotificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Return all notification
     *
     * @return NotificationResource
     */
    public function index()
    {
        $notifications = QueryBuilder::for(Notification::class)
            ->latest()
            ->allowedIncludes(['notifiable.avatar', 'actor.avatar'])
            ->paginate(request()->perPage());

        return NotificationResource::collection($notifications);
    }
}
