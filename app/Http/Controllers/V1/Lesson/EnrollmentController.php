<?php

namespace App\Http\Controllers\V1\Lesson;

use App\Models\MasterLesson;
use App\Http\Controllers\Controller;
use App\Http\Requests\EnrollRequest;
use App\Services\Enrollment\EnrollmentService;
use App\Http\Resources\LessonEnrollmentResource;
use App\Services\Enrollment\Exceptions\InvalidScheduleException;
use App\Services\Enrollment\Exceptions\ConflictScheduleException;
use App\Exceptions\Enrollment\MasterStripeConnectPayoutsDisabledException;
use App\Exceptions\Enrollment\InvalidScheduleException as HttpInvalidScheduleException;
use App\Exceptions\Enrollment\ConflictScheduleException as HttpConflictScheduleException;

class EnrollmentController extends Controller
{
    public function __invoke(EnrollRequest $request, MasterLesson $lesson)
    {
        try {

            throw_if(
                $lesson->user->payouts_enabled == false,
                MasterStripeConnectPayoutsDisabledException::class
            );

            /** @var EnrollmentService */
            $enrollmentService = resolve(
                EnrollmentService::class,
                [
                    'lesson' => $lesson,
                    'student' => $request->user()
                ]
            );

            if ($request->filled('to_learn')) {
                $enrollmentService->setStudentObjective($request->input('to_learn'));
            }

            $schedule = $lesson->schedules()->find($request->input('schedule_id'));

            $enrollment = $enrollmentService->enroll($schedule, $request->input('payment_method_id'));

            $enrollment->load([
                'lesson.user.avatar',
                'lesson.place',
                'master.address',
                'master.avatar',
                'schedule',
                'schedule.lessonEnrollments'
            ]);

            return LessonEnrollmentResource::make($enrollment);
        } catch (ConflictScheduleException $e) {
            throw new HttpConflictScheduleException;
        } catch (InvalidScheduleException $e) {
            throw new HttpInvalidScheduleException($e->getMessage());
        }
    }
}
