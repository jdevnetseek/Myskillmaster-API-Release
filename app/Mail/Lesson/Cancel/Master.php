<?php

namespace App\Mail\Lesson\Cancel;

class Master extends Cancel
{

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.lesson.cancel.master')
            ->with([
                'recipient' => $this->recipient,
                'enrollment' => $this->enrollment,
                'cancelled_at' => $this->cancellationDate(),
                'student' => [
                    'name' => $this->enrollment->student->full_name,
                    'email' => $this->enrollment->student->email,
                ],
                'cancel' => $this->getCancellationReason(),
                'schedule' => $this->getSchedule(),
            ]);
    }

    public function cancellationDate(): string
    {
        $defaultTimezone = 'AEST';

        return $this->enrollment->student_cancelled_at
            ->setTimezone($defaultTimezone)
            ->toFormattedDateString()
            .  ' (' . self::DEFAULT_TIMEZONE . ')';
    }
}
