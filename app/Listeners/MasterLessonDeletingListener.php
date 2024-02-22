<?php

namespace App\Listeners;

use App\Events\MasterLessonDeleting;
use App\Notifications\MasterLessonDeletingNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class MasterLessonDeletingListener implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(MasterLessonDeleting $event)
    {
        $masterLesson = $event->masterLesson;

        // Get the students assigned to the lesson
        $students = $masterLesson->students;

        // Send an email notification to each student
        Notification::send($students, new MasterLessonDeletingNotification($masterLesson));
    }
}
