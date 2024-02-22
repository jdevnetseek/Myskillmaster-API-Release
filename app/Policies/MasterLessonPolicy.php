<?php

namespace App\Policies;

use App\Enums\Plan as EnumsPlan;
use App\Exceptions\NoActiveSubscription;
use App\Exceptions\SubscriptionMaxLimit;
use App\Exceptions\MasterLessonException;
use App\Models\MasterLesson;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;


class MasterLessonPolicy
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
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\MasterLesson  $masterLesson
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, MasterLesson $masterLesson)
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user, User $authUser)
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

        $plan = Plan::where('stripe_plan', $subscription->stripe_plan)->first();

        /** Check if user has reached the maximum number of lessons for the current plan */
        if ($user->lessonCount() >= $plan->number_of_lessons && $plan->slug != EnumsPlan::MASTER_PLAN) {
            throw new MasterLessonException($this->deny(__('error_messages.lesson.max_lesson')));
        }

        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\MasterLesson  $masterLesson
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, MasterLesson $masterLesson)
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

        return $masterLesson->isOwner($user)
            ? $this->allow()
            : $this->deny();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\MasterLesson  $masterLesson
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, MasterLesson $masterLesson)
    {
        // check if master lesson has enrolled students
        if ($masterLesson->hasEnrolledStudent()) {
            return $this->deny(__('error_messages.lesson.enrolled_students'));
        }

        return $masterLesson->isOwner($user) ? $this->allow() : $this->deny(__('error_messages.lesson.owner'));
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\MasterLesson  $masterLesson
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, MasterLesson $masterLesson)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\MasterLesson  $masterLesson
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, MasterLesson $masterLesson)
    {
        //
    }
}
