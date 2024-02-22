<?php

namespace App\Http\Controllers\V1;

use App\Models\Notification;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Requests\NotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\GroupedNotificationResource;

class NotificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Return all notification
     *
     * @return NotificationResource
     */
    public function index()
    {
        $notifications = QueryBuilder::for(Notification::for(auth()->user()))
            ->latest()
            ->allowedIncludes(['notifiable.avatar', 'actor.avatar'])
            ->paginate(request()->perPage());

        return NotificationResource::collection($notifications);
    }

    /**
     * Return today's notifications
     *
     */
    public function today()
    {
        $notifications = QueryBuilder::for(Notification::for(auth()->user()))
            ->today()
            ->latest()
            ->allowedIncludes(['notifiable.avatar', 'actor.avatar'])
            ->get();

        return NotificationResource::collection($notifications);
    }

    /**
     * Returns all notifications this week
     *
     * @return void
     */
    public function thisWeek()
    {
        $notifications = QueryBuilder::for(Notification::for(auth()->user()))
            ->datesBetween(
                now()->startOfWeek(),
                now()->endOfWeek()
            )
            ->latest()
            ->select('*', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->allowedIncludes(['notifiable.avatar', 'actor.avatar'])
            ->get();

        return GroupedNotificationResource::collection($notifications);
    }



    /**
     * Return all read notification
     *
     * @return NotificationResource
     */
    public function read()
    {
        $notifications = QueryBuilder::for(Notification::for(auth()->user()))
            ->latest()
            ->whereNotNull('read_at')
            ->allowedIncludes(['notifiable.avatar', 'actor.avatar'])
            ->paginate(request()->perPage());

        return NotificationResource::collection($notifications);
    }

    /**
     * Return all unread notification
     *
     * @return NotificationResource
     */
    public function unread()
    {
        $notifications = QueryBuilder::for(Notification::for(auth()->user()))
            ->latest()
            ->whereNull('read_at')
            ->allowedIncludes(['notifiable.avatar', 'actor.avatar'])
            ->paginate(request()->perPage());

        return NotificationResource::collection($notifications);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  NotificationRequest $request
     * @return Response
     */
    public function update(NotificationRequest $request)
    {
        $notification = auth()->user()->notifications()->find($request->notification_id);

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json([], 200);
    }
}
