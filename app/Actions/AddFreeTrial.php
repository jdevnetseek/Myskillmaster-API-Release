<?php

namespace App\Actions;

use App\Enums\Plan as EnumsPlan;
use App\Exceptions\FreeTrial;
use App\Exceptions\IncompleteMasterProfile;
use App\Exceptions\SubscriptionFound;
use App\Models\Plan;
use App\Models\User;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Support\Carbon;
use Laravel\Cashier\Subscription;

class AddFreeTrial
{
    public function handle(User $user, Plan $plan, string $name, string $stripeToken)
    {
        $this->validate($user, $plan);

        if ($user->is_subscribed) {
            $this->cancelOldSubscription($user);
        }

        if (!$user->stripe_id) {
            $user->createAsStripeCustomer(['name' => $name ?? $user->full_name]);
        }

        $trialDays = $this->getTrialDays();

        $paymentMethod = (new SubscriptionService)->createPaymentMethod($user, $stripeToken);
        $trialEndsAt = Carbon::now()->addDays($trialDays);

        return $this->subscribeToTrial($user, $plan->name, $plan->stripe_plan, $trialEndsAt, $paymentMethod);
    }

    private function validate(User $user, Plan $plan)
    {
        if (!in_array($plan->slug, [EnumsPlan::MASTER_PLAN, EnumsPlan::MAESTRO_PLAN])) {
            throw new FreeTrial();
        }

        if (!$user->isOnboarded()) {
            throw new IncompleteMasterProfile();
        }
    }

    private function getTrialDays()
    {
        $preLaunchDate = Carbon::parse(config('app.pre_launch_date'));
        $officialLaunchDate = Carbon::parse(config('app.official_launch_date'));
        $now = Carbon::now();

        if ($now->lte($preLaunchDate)) {
            return config('app.pre_launch_trial_days');
        } else if ($now->lt($officialLaunchDate)) {
            return config('app.pre_launch_trial_days');
        } else {
            return config('app.official_launch_trial_days');
        }
    }

    private function subscribeToTrial(User $user, string $planName, string $stripePlan, Carbon $trialEndsAt, $paymentMethod)
    {

        $user->trial_ends_at = $trialEndsAt;
        $user->save();

        return $user->newSubscription($planName, $stripePlan)
            ->trialUntil($trialEndsAt)
            ->create($paymentMethod);
    }

    /**
     * Cancel old subscription that already cancelled by the user but still on grace period.
     * This is to prevent the user from having multiple subscriptions.
     * @param User $user
     * @return void
     */
    private function cancelOldSubscription(User $user): void
    {
        $subscription = Subscription::where('user_id', $user->id)
            ->active()
            ->first();

        $subscription->cancelNow();
    }
}
