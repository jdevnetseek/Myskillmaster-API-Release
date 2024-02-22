<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnrollmentReschedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_schedule_id',
        'to_schedule_id',
        'rescheduled_by',
        'reason',
        'remarks',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(LessonEnrollment::class, 'enrollment_id');
    }

    public function rescheduledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rescheduled_by');
    }
}
