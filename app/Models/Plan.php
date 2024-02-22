<?php

namespace App\Models;

use App\Enums\Plan as EnumsPlan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Plan extends Model
{
    use HasFactory;

    protected $casts = [
        'included' => 'array',
    ];

    /** Accessors */

    public function getAmountAttribute(): string
    {
        return number_format($this->price / 100, 2, '.', '');
    }

    public function getIsSubscribedAttribute(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        $subscription = optional($user->subscription($this->name))->asStripeSubscription();

        if (!$subscription || $subscription->status === 'canceled') {
            return false;
        }

        return true;
    }

    public function getAvailFreeTrialBeforeAttribute()
    {
        $user = auth()->user();

        if (!$user || $this->on_free_trial || $this->slug != EnumsPlan::MASTER_PLAN || !$user->trial_ends_at) {
            return false;
        }

        return true;
    }

    public function getOnFreeTrialAttribute()
    {
        $subscription = optional(auth()->user())->subscription($this->name);

        return $subscription?->onTrial() && $subscription->stripe_status == 'trialing';
    }

    public function getSubscriptionAttribute()
    {
        $user = auth()->user();

        if (!$user) {
            return;
        }

        $subscription = optional($user->subscription($this->name))->asStripeSubscription();

        if (!$subscription || $subscription->status === 'canceled') {
            return;
        }

        $result = $this->buildSubscriptionDetails($subscription);

        return $result;
    }

    /** Helpers */

    private function buildSubscriptionDetails($subscription)
    {
        $carbon = Carbon::now();

        $periodStart = Carbon::createFromTimestamp($subscription->current_period_start)->format('F j');
        $periodEnd = Carbon::createFromTimestamp($subscription->current_period_end)->format('F j');
        $remainingDays = Carbon::createFromTimestamp(
            $subscription->trial_end ?? $subscription->current_period_end
        )->diffInDays($carbon, true);

        $cancelAt = $subscription->cancel_at ? Carbon::createFromTimestamp($subscription->cancel_at)->format('jS F') : null;
        $canceledAt = $subscription->canceled_at ? Carbon::createFromTimestamp($subscription->canceled_at)->format('jS F') : null;
        $nextPaymentDate = !$subscription->cancel_at_period_end ? Carbon::createFromTimestamp($subscription->current_period_end)->format('jS F') : null;

        return [
            'trial_remaining_days'  => $subscription->trial_end ? $remainingDays : null,
            'billing_period'        => $periodStart . ' to ' . $periodEnd,
            'next_payment_date'     => $nextPaymentDate,
            'cancel_at_period_end'  => $subscription->cancel_at_period_end,
            'canceled_at'           => $canceledAt,
            'cancel_at'             => $cancelAt,
            'remaining_days'        => Carbon::createFromTimeStamp($subscription->current_period_end)->diffInDays($carbon, true),
        ];
    }
}
