<?php

namespace App\Services\Subscription;

use App\Models\User;
use App\Models\Plan;
use App\Services\Enrollment\Exceptions\InvalidCardException;
use Laravel\Cashier\Subscription;
use Stripe\PaymentMethod as StripePaymentMethod;
use Stripe\Exception\CardException as StripeCardException;
use Stripe\ErrorObject as StripeErrorObject;
use App\Actions\UpdateLessonStatus;
use App\Mail\Subscription\SubscriptionCancelConfirmation;
use App\Services\Subscription\Exceptions\DefaultPaymentMethodNotFound;
use App\Services\Subscription\Exceptions\NoActiveSubscription;
use App\Services\Subscription\Exceptions\SameSubscriptionFound;
use App\Mail\Subscription\SubscriptionUpdateConfirmation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Stripe\Exception\ApiErrorException;
use App\Exceptions\Subscription\SubscriptionException;

class SubscriptionService
{
    protected $name = null;
    protected $source = null;

    public function setName($name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setSource($source): self
    {
        $this->source = $source;
        return $this;
    }

    public function subscribe(User $user, Plan $plan)
    {
        try {
            DB::beginTransaction();
            $subscription = $user->activeSubscription;

            $this->createUpdateTransaction($user, $plan, $subscription);

            DB::commit();
        } catch (StripeCardException $e) {
            DB::rollBack();
            $this->handleStripeCardErrorException($e);
        } catch (ApiErrorException $e) {
            throw new SubscriptionException($e->getMessage());
        }
    }

    public function createUpdateTransaction(User $user, Plan $plan, $subscription): void
    {
        $name = $user->full_name ??= $this->name;

        if ($subscription) {
            $currentPlan = Plan::where('stripe_plan', $subscription->stripe_plan)->first();

            if ($user->full_name) {
                $user->updateStripeCustomer(['name' => $user->full_name]);
            }

            $this->changePlan($user, $plan, $this->source);

            (new UpdateLessonStatus)->handle($user,  $plan, $currentPlan->price);

            Mail::to($user->email)->send(new SubscriptionUpdateConfirmation($plan, $user, $currentPlan->name));
        } else {
            $this->createSubscription($user, $plan, $this->source, $name);
        }
    }

    /**
     * Create a new subscription for the given user and plan.
     *
     * @param User $user The user for whom to create the subscription.
     * @param Plan $plan The plan to which the user will be subscribed.
     * @param string $stripeToken The Stripe token representing the payment method.
     * @param string $cardName The name of the card associated with the payment method.
     *
     * @return User The updated user object.
     */
    public function createSubscription(User $user, Plan $plan, string $stripeToken, string $cardName): void
    {
        if (!$user->stripe_id) {
            $user->createAsStripeCustomer(['name' => $cardName]);
        }

        $paymentMethod = $this->createPaymentMethod($user, $stripeToken);

        $user->newSubscription($plan->name, $plan->stripe_plan)
            ->skipTrial()
            ->create($paymentMethod);
    }

    /**
     * Cancel the "monthly" subscription but allow the user to continue
     * accessing the content until the end of the current billing cycle
     * @param User $user The user for whom to create the subscription.
     * @return void 
     */
    public function unsubscribePlan(User $user): void
    {
        $subscriptions = $user->subscriptions()->active()->get();

        $subscriptions->map(function ($subscription) {
            $subscription->cancel(false);
        });

        $currentSubscription = $user->activeSubscription;

        Mail::to($user->email)->send(new SubscriptionCancelConfirmation($user, $currentSubscription));
    }

    /**
     * Change user's subscription plan
     * Swap the user's subscription to the new plan, with the remaining days prorated
     * @param User $user
     * @param Plan $plan
     * @return bool
     */
    public function changePlan(User $user, Plan $plan, $stripeToken): void
    {
        $this->ensureThatUserHasNoActiveSubscription($user);

        $this->ensureThatUserIsNotSubscribingToSamePlan($user, $plan);

        $subscription = Subscription::where('user_id', $user->id)->active()->first();

        if ($subscription->onTrial()) {
            $this->cancelOnTrial($user, $plan, $subscription);
        } else {
            $this->swapPlan($user, $plan, $subscription, $stripeToken);
        }
    }

    public function cancelOnTrial(User $user, Plan $plan, $subscription): void
    {
        $this->ensureThatUserHasDefaultPaymentMethod($user);

        // Get the default payment method for the user
        $paymentMethod = $user->defaultPaymentMethod();

        // lets cancel prev subscription if still on trial
        $subscription->cancelNow();

        // create new subscription for the new plan.
        $user->newSubscription($plan->name, $plan->stripe_plan)
            ->skipTrial()
            ->create($paymentMethod->id);
    }

    public function swapPlan(User $user, Plan $plan, $subscription, $stripeToken): void
    {
        try {
            $this->updatePaymentMethod($user, $stripeToken);

            $subscription->name = $plan->name;
            $subscription->swap(
                $plan->stripe_plan,
                [
                    // 'billing_cycle_anchor' => 'now',
                    'proration_behavior' => 'create_prorations',
                ]
            );
        } catch (StripeCardException $e) {
            $this->handleStripeCardErrorException($e);
        } catch (ApiErrorException $e) {
            throw new SubscriptionException($e->getMessage());
        }
    }

    /**
     * Creates a new payment method for the given user with the given Stripe token.
     * @param User $user The user to create the payment method for.
     * @param string $stripeToken The Stripe token representing the payment method.
     * @return StripePaymentMethod The newly created payment method.
     */
    public function createPaymentMethod(User $user, string $stripeToken)
    {
        $paymentMethod = StripePaymentMethod::create([
            'type' => 'card',
            'card' => [
                'token' =>  $stripeToken
            ]
        ], $user->stripeOptions());

        $user->addPaymentMethod($paymentMethod);

        return $paymentMethod;
    }

    /**
     * Creates a new payment method for the given user using the provided Stripe token,
     * and updates the user's default payment method to the newly created one.
     * @param User $user The user for which the payment method is being updated.
     * @param string $stripeToken The Stripe token to be used to create the new payment method.
     * @return StripePaymentMethod The newly created payment method.
     */
    public function updatePaymentMethod(User $user, string $stripeToken)
    {
        $paymentMethod = StripePaymentMethod::create([
            'type' => 'card',
            'card' => [
                'token' =>  $stripeToken
            ]
        ], $user->stripeOptions());

        $user->updateDefaultPaymentMethod($paymentMethod);
        $user->updateDefaultPaymentMethodFromStripe();

        return $paymentMethod;
    }

    /**
     * Resume a previously canceled subscription or create a new one for the given user and plan.
     * If the subscription is on grace period, it will be resumed with no end date.
     *
     * @param User $user The user to resume/create the subscription for
     * @param Plan $plan The plan to subscribe to
     * @param string $stripeToken The Stripe token representing the new payment method to use
     * @return void
     */
    public function resumeSubscription(User $user, Plan $plan): void
    {
        $paymentMethod = $this->updatePaymentMethod($user, $this->source);

        $subscription = $user->subscription($plan->name);

        if ($subscription->onGracePeriod()) {
            $subscription->resume();
            $subscription->update(['ends_at' => null]);
        } else {
            $user->newSubscription($plan->name, $plan->stripe_plan)
                ->create($paymentMethod);
        }
    }

    /**
     * Ensure that the user has no active subscription
     * @param User $user
     * @return void
     */
    private function ensureThatUserHasNoActiveSubscription(User $user): void
    {
        if ($user->subscribed('main')) {
            throw new NoActiveSubscription();
        }
    }

    /**
     * Ensure that the user is not subscribing to the same plan
     * @param User $user
     * @param Plan $plan
     * @return void
     */
    public function ensureThatUserIsNotSubscribingToSamePlan(User $user, Plan $plan): void
    {
        $currentSubscription = Subscription::where('user_id', $user->id)->active()->first();

        if ($currentSubscription->name === $plan->name) {
            throw new SameSubscriptionFound();
        }
    }

    /**
     * Ensure that the user has default payment method
     * @param User $user
     * @return void
     */
    public function ensureThatUserHasDefaultPaymentMethod(User $user): void
    {
        $paymentMethod = $user->defaultPaymentMethod();

        if (!$paymentMethod) {
            throw new DefaultPaymentMethodNotFound();
        }
    }

    /**
     * @throws InvalidCardException
     */
    private function handleStripeCardErrorException(StripeCardException $exception)
    {
        $message = $exception->getMessage();

        if ($exception->getStripeCode() === StripeErrorObject::CODE_CARD_DECLINED) {
            /**
             * @see https://stripe.com/docs/declines/codes
             */
            switch ($exception->getDeclineCode()) {
                case 'fraudulent':
                case 'lost_card':
                case 'stolen_card':
                case 'merchant_blacklist':
                    $message = 'The card was declined for an unknown reason';
                    break;
            }
        }

        throw new InvalidCardException($message);
    }
}
