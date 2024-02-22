<?php

namespace App\Mail\Lesson\Cancel;

class Student extends Cancel
{

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.lesson.cancel.student')
            ->with([
                'recipient' => $this->recipient,
                'enrollment' => $this->enrollment,
                'cancelled_at' => $this->cancellationDate(),
                'master' => [
                    'name' => $this->enrollment->master->full_name,
                    'email' => $this->enrollment->master->email,
                ],
                'cancel' => $this->getCancellationReason(),
                'schedule' => $this->getSchedule(),
            ]);
    }

    public function cancellationDate(): string
    {
        $defaultTimezone = 'AEST';

        return $this->enrollment->master_cancelled_at
            ->setTimezone($defaultTimezone)
            ->toFormattedDateString()
            .  ' (' . self::DEFAULT_TIMEZONE . ')';
    }
}
