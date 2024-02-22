<?php

namespace App\Services\Payout;

use Stripe\Balance;
use App\Models\User;
use Stripe\StripeClient;
use Illuminate\Support\Arr;
use Laravel\Cashier\Cashier;
use App\Models\LessonEnrollment;
use Illuminate\Support\Collection;

class BalanceService
{
    public function __construct(protected User $user)
    {
    }

    /**
     * Undocumented function
     *
     * @return array [<current_week>, <available>, <pending>]
     */
    public function get(): array
    {
        /** @var Stripe\Balance */
        $response = Balance::retrieve(Cashier::stripeOptions([
            'stripe_account' => $this->user->stripeConnectId()
        ]));

        $available = Arr::first($response['available']);
        $pending = Arr::first($response['pending']);

        if ($pending->amount < 0) {
            // if pending amount is negative value, deduct it in to the available balance
            // Why addition operation here? because if we use subtraction it will simply add
            // because multiplying two negative numbers will result to positive
            // e.g amount = available amount - (-pending amount) = available + pending
            $available->amount += $pending->amount;
        }

        return [
            'available' => [
                'amount' => round($available->amount / 100, 2),
                'currency' => $available->currency,
            ],
            'pending' => [
                'amount' => round($pending->amount / 100, 2),
                'currency' => $pending->currency
            ],
        ];
    }
}
