<?php

namespace App\Mail\Lesson;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Models\LessonEnrollment;
use App\Enums\RescheduleLessonReason;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class LessonRescheduled extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(public LessonEnrollment $enrollment, public User $recipient)
    {
        $this->afterCommit();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = config('app.name') . ' - Your lesson has been rescheduled';

        return $this->markdown('emails.lesson.reschedule')
            ->subject($subject)
            ->with([
                'schedule' => $this->scheduleDetails(),
                'reschedule' => $this->rescheduleReaon(),
                'web_app_enrollment_detail_link' => $this->getWebAppEnrollmentDetailLink()
            ]);
    }

    protected function scheduleDetails(): array
    {
        $defaultTimezone = 'AEST';
        $schedule = $this->enrollment->schedule;

        $formattedStartDate =  $schedule->schedule_start->setTimezone($defaultTimezone)->toDayDateTimeString()
            . " ($defaultTimezone)";

        $formattedEndDate =  $schedule->schedule_end->setTimezone($defaultTimezone)->toDayDateTimeString()
            . " ($defaultTimezone)";

        return [
            'start_date' => $formattedStartDate,
            'end_date' => $formattedEndDate,
            'timezone' => $defaultTimezone,
        ];
    }

    protected function rescheduleReaon()
    {
        /** @var App\Models\EnrollmentReschedule */
        $reschedule = $this->enrollment->latestReschedule();

        return [
            'reason' => RescheduleLessonReason::getDescription($reschedule->reason),
            'remarks' => $reschedule->remarks,
        ];
    }

    protected function getWebAppEnrollmentDetailLink(): string
    {
        if ($this->enrollment->master_id === $this->recipient->getKey()) {
            $link = config('app.web.to_teach_url') . '/' . $this->enrollment->schedule_id;
        } else {
            $link = config('app.web.to_learn_url') . '/' . $this->enrollment->reference_code;
        }

        return $link;
    }
}
