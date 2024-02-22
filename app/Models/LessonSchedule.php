<?php

namespace App\Models;

use App\Enums\ScheduleRecurrences;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class LessonSchedule extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'master_lesson_id',
        'schedule_start',
        'schedule_end',
        'slots',
        'dows',
        'recurrence',
        'frequency',
        'period',
        'duration_in_hours'
    ];

    protected $casts = [
        'schedule_start' => 'datetime',
        'schedule_end' => 'datetime'
    ];

    public function masterLesson(): BelongsTo
    {
        return $this->belongsTo(MasterLesson::class, 'master_lesson_id');
    }

    public function lessonEnrollments(): HasMany
    {
        return $this->hasMany(LessonEnrollment::class, 'schedule_id');
    }

    public function master(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            MasterLesson::class,
            'id',
            'id',
            'master_lesson_id',
            secondLocalKey: 'user_id' // local key on MasterLesson
        );
    }

    public function masterProfile(): HasOneThrough
    {
        return $this->master()
            ->with('address', 'masterProfile');
    }

    public function enrollStudents(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            LessonEnrollment::class,
            'schedule_id', // foreign key on lesson_enrollments
            'id',
            secondLocalKey: 'student_id' // local key on master lessons
        );
    }

    public function studentProfile(): HasManyThrough
    {
        return $this->enrollStudents()
            ->select('lesson_enrollments.to_learn', 'users.*')
            ->with('address');
    }

    /** Helpers */

    public function numberOfStudentsEnrolled(): int
    {
        return  $this->lessonEnrollments->count();
    }

    public function hasAvailableSlotsForEnrollment(): bool
    {
        $stripe_plan = $this->masterLesson->user->activeSubscription->stripe_plan;

        $plan = Plan::where('stripe_plan', $stripe_plan)->first();
        
        $isLimit = !$plan->max_students ? true : $this->masterLesson->enrollments->count() < $plan->max_students;

        return $this->lessonEnrollments()->notCancelled()->count() < $this->slots && $isLimit;
    }

    public function isUpcoming(): bool
    {
        return now()->lessThan($this->schedule_start);
    }

    public function isOngoing(): bool
    {
        return now()->between($this->schedule_start, $this->schedule_end);
    }

    public function isCompleted(): bool
    {
        return now()->greaterThan($this->schedule_end);
    }

    public function isAttendanceConfirmed(): bool
    {
        return $this->lessonEnrollments()->notCancelled()->ratedOrAttended()->exists();
    }

    /** Scopes */

    public function scopeAsMaster($query, User $user)
    {
        $query->whereMasterId($user->getKey());
    }

    public function scopeUpcoming($query)
    {
        $query->where('schedule_start', '>', now());
    }

    public function scopeOngoing($query)
    {
        $now = now();

        $query->where('schedule_start', '<=', $now)
            ->where('schedule_end', '>=', $now);
    }

    public function scopeCompleted($query)
    {
        $query->where('schedule_end', '<', now());
    }

    public function scopeHasMasterLesson($query, User $user)
    {
        $query->whereHas('masterLesson', function ($query) use ($user) {
            $query->ownedBy($user);
        });
    }

    public function scopeHasStudentEnrollment($query, User $user)
    {
        $query->whereHas('lessonEnrollments', function ($query) use ($user) {
            $query->notCancelled()
                ->asMaster($user)
                ->ratedOrAttended();
        });
    }

    public function scopeHasStudentNotConfirmedEnrollment($query, User $user)
    {
        $query->whereHas('lessonEnrollments', function ($query) use ($user) {
            $query->notCancelled()
                ->asMaster($user)
                ->notRatedOrAttended();
        });
    }

    public function scopeHasConflict(Builder $query, $startDate, $endDate, $dows, $timeZone)
    {
        $start = Carbon::parse($startDate, $timeZone);
        $end = Carbon::parse($endDate, $timeZone);
        $startUtc = $start->setTimezone('UTC');
        $endUtc = $end->setTimezone('UTC');

        $query->where(function ($query) use ($dows) {
            foreach ($dows as $dow) {
                $query->orWhereRaw("find_in_set('{$dow}', dows)");
            }
        })
            ->where(function ($query) use ($startUtc, $endUtc) {
                $query->whereRaw("Date(schedule_start) <= DATE('{$endUtc}')")
                    ->WhereRaw("Date(schedule_end) >= DATE('{$startUtc}')")
                    ->whereRaw("Time(schedule_start) < TIME('{$endUtc}') ")
                    ->whereRaw("Time(schedule_end) > TIME('{$startUtc}') ");
            });
    }

    // ACCESSORS

    public function getDowsAttribute()
    {
        return implode(', ', explode(',', $this->attributes['dows']));
    }
}
