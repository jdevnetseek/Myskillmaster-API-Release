<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use App\Models\Interfaces\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;
use Staudenmeir\EloquentJsonRelations\HasJsonRelationships;

class Notification extends DatabaseNotification
{
    use HasJsonRelationships;

    protected $casts = [
        'data' => 'json',
    ];

    /**
     * The actor of the notification
     *
     */
    public function actor()
    {
        return $this->belongsTo(User::class, 'data->actor_id');
    }

    /**
     * Filter based on the givent notifiable
     *
     * @param Builder $query
     * @param Notifiable $notifiable
     * @return void
     */
    public function scopeFor(Builder $query, Notifiable $notifiable)
    {
        $query->where('notifiable_type', get_class($notifiable))
            ->where('notifiable_id', $notifiable->getKey());
    }

    /**
     * Returns only today's notifications
     *
     * @param Builder $query
     * @return void
     */
    public function scopeToday(Builder $query)
    {
        $query->whereDate('created_at', today());
    }

    /**
     * Filter between two dates
     *
     * @param Builder $query
     * @param Carbon $start
     * @param Carbon $end
     * @return void
     */
    public function scopeDatesBetween(Builder $query, Carbon $start, Carbon $end)
    {
        $query->whereBetween('created_at', [$start, $end]);
    }
}
