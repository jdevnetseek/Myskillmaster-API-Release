<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\InteractsWithUserPayout;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LessonEnrollment extends Model
{
    use HasFactory;
    use InteractsWithUserPayout;

    protected $guarded = [
        'id', 'reference_code',
    ];

    protected $casts = [
        'refunded_at' => 'datetime',
        'paid_at' => 'datetime',
        'student_cancelled_at' => 'datetime',
        'master_cancelled_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->reference_code = static::generateReferenceCode();
        });
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function master()
    {
        return $this->belongsTo(User::class, 'master_id');
    }

    public function lesson()
    {
        return $this->belongsTo(MasterLesson::class, 'lesson_id');
    }

    public function payments()
    {
        return $this->hasMany(EnrollmentPayment::class);
    }

    public function schedule()
    {
        return $this->hasOne(LessonSchedule::class, 'id', 'schedule_id');
    }

    public function reschedules()
    {
        return $this->hasMany(EnrollmentReschedule::class, 'enrollment_id')
            ->latest();
    }

    /** Scopes */

    public function scopeAsMaster($query, User $user)
    {
        $query->whereMasterId($user->getKey());
    }

    public function scopeAsStudent($query, User $user)
    {
        $query->whereStudentId($user->getKey());
    }

    public function scopeUserAsMasterOrStudent($query, User $user)
    {
        return $query->asMaster($user)
            ->orWhere
            ->asStudent($user);
    }

    public function scopeNotCancelled($query)
    {
        return $query->whereNull('master_cancelled_at')
            ->whereNull('student_cancelled_at');
    }

    public function scopeNotRatedOrAttended($query)
    {
        return $query->whereNull('master_rated_at')
            ->orWhereNull('is_student_attended');
    }

    public function scopeNotRated($query)
    {
        return $query->whereNull('master_rated_at');
    }

    public function scopeAttendanceNotConfirmed($query)
    {
        return $query->whereNull('is_student_attended');
    }

    public function scopeRatedOrAttended($query)
    {
        return $query->whereNotNull('master_rated_at')
            ->orWhereNotNull('is_student_attended');
    }

    public function scopeHasPastLessonSchedule($query)
    {
        $query->whereHas('schedule', function ($query) {
            $query->where('schedule_end', '<', now());
        });
    }

    public function scopePaid($query)
    {
        return $query->whereNotNull('paid_at');
    }

    /** Helpers */

    public function isCancelledByStudent(): bool
    {
        return filled($this->student_cancelled_at);
    }

    public function isCancelledByMaster(): bool
    {
        return filled($this->master_cancelled_at);
    }

    public function isCancelled(): bool
    {
        return $this->isCancelledByMaster() || $this->isCancelledByStudent();
    }

    public function latestReschedule(): EnrollmentReschedule
    {
        return $this->reschedules()->first();
    }

    public function isRefundable(): bool
    {
        // can be refundable within 48 hours after enrollment
        $maxHoursForRefund = 48;

        return is_null($this->refunded_at)
            && now()->diffInHours($this->created_at) <= $maxHoursForRefund;
    }

    public function isAttendanceConfirmed(): bool
    {
        return filled($this->is_student_attended);
    }

    /** Misc */
    public static function generateReferenceCode(): string
    {
        do {
            // Format: ymdhi[random10characters]
            // eg: 230330113455 (date: March 30, 2023 11:34:55)
            $code = date('ymdhis') . Str::lower(Str::random(10));
        } while (static::where('reference_code', $code)->exists());

        return $code;
    }

    /** create status attribute to determine the status of the
     * lesson if it is upcoming, ongoing or completed based
     * on the start date and end date of schedule
     */
    public function getStatusAttribute()
    {
        if ($this->schedule->isUpcoming()) {
            return 'upcoming';
        }

        if ($this->schedule->isOngoing()) {
            return 'ongoing';
        }

        if ($this->schedule->isCompleted()) {
            return 'completed';
        }
    }
}
