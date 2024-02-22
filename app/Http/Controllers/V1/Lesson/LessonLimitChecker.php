<?php

namespace App\Http\Controllers\V1\Lesson;

use App\Enums\ErrorCodes;
use App\Enums\Plan as EnumsPlan;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LessonLimitChecker extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $subscription = $request->user()
            ->subscriptions()
            ->active()
            ->first();

        $plan = Plan::where('stripe_plan', $subscription->stripe_plan)->first();

        /** Check if user has reached the maximum number of lessons for the current plan */
        if ($request->user()->lessonCount() >= $plan->number_of_lessons && $plan->slug != EnumsPlan::MASTER_PLAN) {
            return $this->respondWithError(
                ErrorCodes::MASTER_LESSON_ERROR,
                Response::HTTP_FORBIDDEN,
                'You have reached the maximum number of lessons for your current plan. Please upgrade your plan to continue using the service.'
            );
        }

        return response()->json([], Response::HTTP_OK);
    }
}
