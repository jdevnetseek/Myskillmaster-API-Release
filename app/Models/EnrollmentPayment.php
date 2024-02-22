<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnrollmentPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'amount',
        'currency',
        'type',
        'refund_reason',
        'paid_at',
    ];

    public function enrollment()
    {
        return $this->belongsTo(LessonEnrollment::class);
    }

    public function scopeType($query, string $type)
    {
        $query->whereType($type);
    }
}
