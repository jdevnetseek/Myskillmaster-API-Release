<?php

namespace App\Services\Subscription;

use App\Enums\MediaCollectionType;
use App\Models\EnrollmentPayment;
use App\Models\LessonEnrollment;
use App\Models\MasterProfile;
use App\Models\Media;
use Laravel\Cashier\Cashier;
use App\Models\User;
use App\Services\MasterProfile\Exceptions\MasterProfileException;
use Illuminate\Support\Arr;
use Stripe\StripeClient;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Support\Facades\DB;

class MasterProfileService
{
    private StripeClient $stripeClient;

    public function __construct(protected User $user)
    {
        $this->stripeClient = resolve(
            StripeClient::class,
            [
                'config' => Cashier::stripeOptions([
                    'stripe_account' => $this->user->stripeConnectId()
                ])
            ]
        );
    }

    public function removeMasterProfile()
    {
        $this->ensureThatMasterHasNoOngoingOrUpcomingLessons();

        $this->ensureThatAllPayoutsAreCompleted();

        DB::transaction(function () {
            $this->deleteMasterPortfolio();
            $this->deleteMasterProfile();
            $this->unsubscribeFromPlan();
            $this->removeLessonEnrollmentPayment();
            $this->removeLessonEnrollment();
            $this->removeMasterLesson();
        });
    }

    public function ensureThatMasterHasNoOngoingOrUpcomingLessons()
    {
        $checkEnrollment = LessonEnrollment::asMaster($this->user)
            ->notCancelled()
            ->whereHas('schedule', function ($query) {
                $query->where('schedule_start', '<=', now())
                    ->where('schedule_end', '>=', now());
            })
            ->exists();

        if ($checkEnrollment) {
            throw new MasterProfileException('You cannot delete your profile while you have ongoing or upcoming lessons. Please refund your students first.');
        }
    }

    public function ensureThatAllPayoutsAreCompleted()
    {
        if ($this->user->stripeConnectId()) {
            $balance = $this->stripeClient->balance->retrieve();
            $available = Arr::first($balance['available']);
            $pending = Arr::first($balance['pending']);

            if ($available['amount'] > 0 || $pending['amount'] > 0) {
                throw new MasterProfileException('You cannot delete your profile while you have pending payouts. Please wait for your payouts to be transferred to your bank account.');
            }
        }
    }

    private function deleteMasterPortfolio(): void
    {
        if ($this->user->masterProfile) {
            Media::where('model_id', $this->user->masterProfile->id)
                ->where('model_type', MasterProfile::class)
                ->where('collection_name', MediaCollectionType::PORTFOLIO)
                ->delete();
        }
    }

    private function deleteMasterProfile(): void
    {
        $this->user->masterProfile()->delete();
    }

    private function removeMasterLesson(): void
    {
        $this->user->lessons()->delete();
    }

    private function removeLessonEnrollmentPayment(): void
    {
        $enrollment = LessonEnrollment::asMaster($this->user)
            ->pluck('id');

        EnrollmentPayment::whereIn('lesson_enrollment_id', $enrollment)->delete();
    }

    private function removeLessonEnrollment(): void
    {
        LessonEnrollment::asMaster($this->user)->delete();
    }

    private function unsubscribeFromPlan(): void
    {
        if ($this->user->activeSubscription) {
            (new SubscriptionService)->unsubscribePlan($this->user);
        }
    }
}
