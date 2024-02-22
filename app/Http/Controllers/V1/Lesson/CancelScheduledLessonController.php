<?php

namespace App\Http\Controllers\V1\Lesson;

use App\Events\LessonEnrollment\LessonCancelled;
use App\Models\MasterLesson;
use Illuminate\Http\Request;
use App\Models\LessonSchedule;
use App\Models\LessonEnrollment;
use App\Http\Controllers\Controller;
use App\Services\Enrollment\CancelEnrollmentService;
use App\Http\Requests\StudentLessonCancellationRequest;
use App\Services\Enrollment\Exceptions\LessonCancellationException;
use App\Services\Enrollment\Exceptions\StudentLessonCancellationException;
use App\Exceptions\Enrollment\LessonCancellationException as HttpLessonCancellationException;

class CancelScheduledLessonController extends Controller
{
    public function cancelByMaster(StudentLessonCancellationRequest $request, LessonSchedule $schedule)
    {
        try {
            resolve(CancelEnrollmentService::class, ['user' => $request->user()])
                ->setCancellationReason($request->input('cancellation_reason'))
                ->setCancellationRemarks($request->input('cancellation_remarks'))
                ->masterBulkCancellationBySchedule($schedule);

            return $this->respondWithEmptyData();
        } catch (LessonCancellationException $e) {
            throw new HttpLessonCancellationException($e->getMessage());
        }
    }

    public function cancelByStudent(StudentLessonCancellationRequest $request, LessonEnrollment $enrollment)
    {
        try {

            resolve(CancelEnrollmentService::class, ['user' => $request->user()])
                ->setCancellationReason($request->input('cancellation_reason'))
                ->setCancellationRemarks($request->input('cancellation_remarks'))
                ->cancelByStudent($enrollment);

            return $this->respondWithEmptyData();
        } catch (LessonCancellationException $e) {
            throw new HttpLessonCancellationException($e->getMessage());
        }
    }
}
