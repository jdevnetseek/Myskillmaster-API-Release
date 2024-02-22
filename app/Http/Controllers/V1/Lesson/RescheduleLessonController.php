<?php

namespace App\Http\Controllers\V1\Lesson;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\LessonSchedule;
use App\Models\LessonEnrollment;
use BenSampo\Enum\Rules\EnumValue;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Enums\RescheduleLessonReason;
use App\Events\LessonEnrollment\LessonRescheduledByStudent;
use App\Mail\Lesson\LessonRescheduled;
use App\Http\Resources\LessonToTeachResource;
use App\Http\Resources\LessonEnrollmentResource;
use App\Services\Enrollment\RescheduleLessonService;
use App\Services\Enrollment\Exceptions\InvalidUserException;
use App\Services\Enrollment\Exceptions\InvalidScheduleException;
use App\Services\Enrollment\Exceptions\NoSlotsAvailableException;
use App\Exceptions\Enrollment\InvalidScheduleException as HttpInvalidScheduleException;

class RescheduleLessonController extends Controller
{
    public function rescheduleByStudent(Request $request, LessonEnrollment $enrollment)
    {
        $request->validate([
            'new_schedule_id' => ['required'],
            'reason' => ['required', 'string', new EnumValue(RescheduleLessonReason::class)],
            'remarks' => ['string', 'max:500', 'nullable'],
        ]);

        $newSchedule = LessonSchedule::findOrFail($request->input('new_schedule_id'));

        try {

            $enrollment = (new RescheduleLessonService($newSchedule, $request->user()))
                ->setReason($request->input('reason'))
                ->setRemarks($request->input('remarks'))
                ->reschedule($enrollment);

            Mail::to($enrollment->master->email)
                ->send(new LessonRescheduled($enrollment, $enrollment->master));

            return LessonEnrollmentResource::make($enrollment);
        } catch (InvalidScheduleException $e) {
            throw new HttpInvalidScheduleException($e->getMessage());
        }
    }

    public function bulkRescheduleByMaster(Request $request, LessonSchedule $schedule)
    {
        $request->validate([
            'new_schedule_id' => ['required'],
            'reason' => ['required', 'string', new EnumValue(RescheduleLessonReason::class)],
            'remarks' => ['string', 'max:500', 'nullable'],
        ]);

        $newSchedule = LessonSchedule::findOrFail($request->input('new_schedule_id'));

        $user = $request->user();

        try {

            $rescheduleService = (new RescheduleLessonService($newSchedule, $user))
                ->setReason($request->input('reason'))
                ->setRemarks($request->input('remarks'));

            /**
             * Right now, the schedule has only one enrollment record
             * However, we had a feature in backlog that students can enroll to a same schedule
             */
            $schedule->lessonEnrollments()
                ->asMaster($user)
                ->notCancelled()
                ->get()
                ->each(function ($enrollment) use ($rescheduleService) {
                    $rescheduleService->reschedule($enrollment);
                    Mail::to($enrollment->student->email)
                        ->send(new LessonRescheduled($enrollment, $enrollment->student));
                });

            return LessonToTeachResource::make($newSchedule);
        } catch (InvalidScheduleException $e) {
            throw new HttpInvalidScheduleException($e->getMessage());
        } catch (InvalidUserException $e) {
            abort(Response::HTTP_FORBIDDEN, $e->getMessage());
        }
    }
}
