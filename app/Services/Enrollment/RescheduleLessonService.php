<?php

namespace App\Services\Enrollment;

use App\Models\User;
use App\Models\LessonSchedule;
use App\Models\LessonEnrollment;
use Illuminate\Support\Facades\DB;
use App\Services\Enrollment\Exceptions\InvalidScheduleException;
use App\Services\Enrollment\Exceptions\InvalidUserException;
use App\Services\Enrollment\Exceptions\NoSlotsAvailableException;

class RescheduleLessonService
{
    protected string $reason;
    protected ?string $remarks;

    public function __construct(public LessonSchedule $newSchedule, protected User $user)
    {
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;
        return $this;
    }

    public function setRemarks(?string $remarks): self
    {
        $this->remarks = $remarks;
        return $this;
    }

    /**
     * @throws App\Services\Enrollment\Exceptions\NoSlotsAvailableException
     * @throws App\Services\Enrollment\Exceptions\InvalidUserException
     */
    public function reschedule(LessonEnrollment $enrollment): LessonEnrollment
    {
        if ($this->newSchedule->getKey() == $enrollment->schedule_id) {
            throw new InvalidScheduleException('New schedule must be different to the current schedule');
        }

        $this->assertThatEnrollmentCanBeReschedule($enrollment);

        $this->assertThatTheNewScheduleIsValid($enrollment);

        $this->assertThatUserIsMasterOrStudent($enrollment);

        DB::beginTransaction();

        $enrollment->reschedules()->create([
            'from_schedule_id' => $enrollment->schedule_id,
            'to_schedule_id' => $this->newSchedule->getKey(),
            'reason' => $this->reason,
            'remarks' => $this->remarks,
            'rescheduled_by' => $this->user->getKey(),
        ]);

        // change schedule
        $enrollment->update([
            'schedule_id' => $this->newSchedule->getKey(),
        ]);

        DB::commit();

        $enrollment->refresh();

        return $enrollment;
    }

    /** @throws App\Services\Enrollment\Exceptions\InvalidScheduleException  */
    private function assertThatEnrollmentCanBeReschedule(LessonEnrollment $enrollment)
    {
        $currentSchedule = $enrollment->schedule;

        if ($currentSchedule->isOngoing() || $currentSchedule->isCompleted()) {
            throw new InvalidScheduleException('Cannot be reschedule. The lesson might be ongoing or completed.');
        }
    }

    /** @throws App\Services\Enrollment\Exceptions\InvalidUserException  */
    private function assertThatUserIsMasterOrStudent(LessonEnrollment $enrollment): void
    {
        if (in_array($this->user->getKey(), [$enrollment->student_id, $enrollment->master_id]) === false) {
            throw new InvalidUserException('Invalid user. User must be the student or the master.');
        }
    }

    /**
     * @throws  App\Services\Enrollment\Exceptions\InvalidScheduleException
     * @throws  App\Services\Enrollment\Exceptions\NoSlotsAvailableException
    */
    private function assertThatTheNewScheduleIsValid(LessonEnrollment $enrollment)
    {
        // check if new schedule belongs to the lesson
        if($enrollment->lesson->schedules()->whereId($this->newSchedule->getKey())->doesntExist()) {
            throw new InvalidScheduleException('Schedule not found');
        }

        if ($this->newSchedule->isUpcoming() === false) {
            throw new InvalidScheduleException('Schedule is not valid. Schedule might be ongoing or already concluded.');
        }

        if ($this->newSchedule->hasAvailableSlotsForEnrollment() === false) {
            throw new NoSlotsAvailableException;
        }
    }
}
