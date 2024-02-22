<?php

namespace App\Mail\Lesson\Cancel;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Models\LessonEnrollment;
use App\Enums\CancellationReason;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

abstract class Cancel extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    const DEFAULT_TIMEZONE = 'AEST';

    public function __construct(
        protected LessonEnrollment $enrollment,
        protected User $actor,
        protected User $recipient
    ) {
        $this->afterCommit();
        $this->subject = config('app.name') . ' - Lesson Cancelled';
    }

    /**
     *
     *
     * @return array [<start>, <end>, <tz>]
     */
    public function getSchedule(): array
    {
        $schedule = $this->enrollment->schedule;

        return [
            'start' => $schedule->schedule_start
                ->setTimezone(self::DEFAULT_TIMEZONE)
                ->toDayDateTimeString(),

            'end' => $schedule->schedule_end
                ->setTimezone(self::DEFAULT_TIMEZONE)
                ->toDayDateTimeString(),

            'tz' => self::DEFAULT_TIMEZONE,
        ];
    }

    /**
     *
     * @return array [<reason>,<remarks>]
     */
    public function getCancellationReason(): array
    {
        return [
            'reason'  => CancellationReason::getDescription($this->enrollment->cancellation_reason),
            'remarks' => $this->enrollment->cancellation_remarks,
        ];
    }
}
