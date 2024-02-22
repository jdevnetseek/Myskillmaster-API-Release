<?php

use App\Models\User;
use App\Models\LessonEnrollment;
use App\Models\EnrollmentPayment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Queue\ShouldQueue;

class LessonCancelled implements ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public LessonEnrollment $lessonEnrollment,
        public User $actor
    ) {
    }
}
