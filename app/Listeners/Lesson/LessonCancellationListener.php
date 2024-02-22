<?php

namespace App\Listeners\Lesson;

use App\Mail\Lesson\Cancel\Master;
use App\Mail\Lesson\Cancel\Student;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\LessonEnrollment\LessonCancelled;

class LessonCancellationListener implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(LessonCancelled $event)
    {
        $recipient = $event->actor->getKey() === $event->enrollment->master_id
            ? $event->enrollment->student
            : $event->enrollment->master;

        $mail = Mail::to($recipient->email);

        if ($recipient->getKey() === $event->enrollment->master_id) {
            $mail->send(new Master($event->enrollment, $event->actor, $recipient));
        } else {
            $mail->send(new Student($event->enrollment, $event->actor, $recipient));
        }
    }
}
