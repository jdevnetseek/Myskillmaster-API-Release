<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stripe\Payout;

class UserPayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'payout_id',
        'amount',
        'currency',
        'status',
        'is_initiated_by_user',
        'arrival_date',
        'failure_code',
        'failure_message',
    ];

    protected $casts = [
        'is_initiated_by_user' => 'boolean',
        'arrival_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function paidOutEnrollments()
    {
        return $this->belongsToMany(LessonEnrollment::class, 'lesson_enrollment_payouts');
    }

    public function scopeFailed($query)
    {
        return $query->whereStatus(Payout::STATUS_FAILED);
    }
}
