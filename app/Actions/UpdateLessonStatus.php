<?php

namespace App\Actions;

use App\Models\Plan;
use App\Models\User;

class UpdateLessonStatus
{
    public function handle(User $user, Plan $plan, int $lessonOldPlanPrice): bool
    {
        $lessonCount  = $user->lessonCount();
        $newPlanLimit = $plan->number_of_lessons;

        if ($plan->price < $lessonOldPlanPrice) {
            return $this->handleDowngrade($user, $lessonCount, $newPlanLimit);
        } else {
            return $this->handleUpgrade($user, $newPlanLimit);
        }

        return true;
    }

    /**
     * Downgrades a user's account based on the number of lessons they have access to in their current plan.
     *
     * @param User $user The user to downgrade.
     * @param int $lessonCount The number of lessons the user currently has access to.
     * @param int $planLimit The maximum number of lessons the user is allowed to have access to.
     * 
     * @return bool Returns true if the user's account was downgraded successfully, false otherwise.
     * 
     * @throws \Exception If an unexpected error occurs during execution.
     */
    public function handleDowngrade(User $user, int $lessonCount, int $planLimit)
    {
        // Retrieve the latest lesson(s) created
        $lessonsToHide = $user->lessons()
            ->whereActive(1)
            ->skip($planLimit)
            ->latest()
            ->take(max(0, $lessonCount - $planLimit))
            ->pluck('id');

        // Deactivate lessons that exceed the plan limit
        $user->lessons()
            ->whereIn('id', $lessonsToHide)
            ->update(['active' => false]);

        return true;
    }

    /**
     * If user upgrade from limited plan to other higher tier plans
     */
    public function handleUpgrade(User $user, int $planLimit): bool
    {
        $activeLessonCount = $user->lessons()->where('active', 1)->count();
        $inactiveLessons   = $user->lessons()->where('active', 0);
        $unlimitedLessons  = -1;

        if ($planLimit !== $unlimitedLessons) {
            $remainingLessonCount = $planLimit - $activeLessonCount;
            $inactiveLessons = $inactiveLessons->take($remainingLessonCount);
        }

        $inactiveLessons->update(['active' => true]);

        return true;
    }
}
