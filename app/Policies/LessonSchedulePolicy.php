<?php

namespace App\Policies;

use App\Exceptions\MasterLessonException;
use App\Models\LessonSchedule;
use App\Models\MasterLesson;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LessonSchedulePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LessonSchedule  $lessonSchedule
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, LessonSchedule $lessonSchedule)
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     *  @param  \App\Models\LessonSchedule  $lessonSchedule
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user, LessonSchedule $lessonSchedule)
    {
        $subscription = $user->subscriptions()
            ->active()
            ->first();

        /** Check if user has active subscription */
        if (!$subscription) {
            throw new MasterLessonException($this->deny(__('error_messages.lesson.no_active_subscription')));
        }

        /** Check if user has active but cancelled subscription */
        if ($subscription->cancelled() && !$user->masterProfile()) {
            throw new MasterLessonException($this->deny(__('error_messages.lesson.cancelled_subscription')));
        }

        // Only the owner of the master lesson can create a schedule
        return $user->id === $lessonSchedule->masterLesson->user_id ?
            $this->allow() : $this->deny(__('error_messages.lesson_schedule_create.owner'));
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LessonSchedule  $lessonSchedule
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, LessonSchedule $lessonSchedule)
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LessonSchedule  $lessonSchedule
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, LessonSchedule $lessonSchedule)
    {
        // Check if there are no students enrolled in the schedule
        if ($lessonSchedule->numberOfStudentsEnrolled() > 0) {
            return $this->deny(__('error_messages.lesson_schedule_delete.has_student_enrolled'));
        }

        // Check if the authenticated user is the owner of the lesson
        return $user->id === $lessonSchedule->masterLesson->user_id
            ? $this->allow() : $this->deny(__('error_messages.lesson_schedule_delete.owner'));
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LessonSchedule  $lessonSchedule
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, LessonSchedule $lessonSchedule)
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LessonSchedule  $lessonSchedule
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, LessonSchedule $lessonSchedule)
    {
        return true;
    }
}
