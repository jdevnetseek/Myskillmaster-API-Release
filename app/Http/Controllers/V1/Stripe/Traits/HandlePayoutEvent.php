<?php

namespace App\Http\Controllers\V1\Stripe\Traits;

use Carbon\Carbon;
use Stripe\Payout;
use App\Models\User;
use App\Models\UserPayout;
use App\Mail\Payout\PayoutFailed;
use App\Mail\Payout\SuccessPayout;
use Illuminate\Support\Facades\Mail;

trait HandlePayoutEvent
{
    /**
     * We will handle payout created event
     * so we can store payout details if it was created in Stripe dashboard.
     */
    public function handlePayoutCreated(array $payload)
    {
        $connectId = data_get($payload, 'account');

        if (($user = User::whereStripeConnectId($connectId)->first())) {
            $data = data_get($payload, 'data.object');

            if ($user->payouts()
                ->wherePayoutId(data_get($payload, 'data.object.id'))
                ->doesntExist()
            ) {
                $user->payouts()->create([
                    'payout_id' => data_get($data, 'id'),
                    'amount' => data_get($data, 'amount') / 100,
                    'currency' => data_get($data, 'currency'),
                    'status' => data_get($data, 'status'),
                    'is_initiated_by_user' => false,
                    'arrival_date' => Carbon::createFromTimestamp(data_get($data, 'arrival_date')),
                ]);
            }
        }
    }

    public function handlePayoutUpdated(array $payload)
    {
        $id = data_get($payload, 'data.object.id');

        if (($payout = UserPayout::wherePayoutId($id)->first())) {
            $payout->update([
                'status' => data_get($payload, 'data.object.status'),
            ]);
        }

        return $this->successMethod();
    }

    public function handlePayoutPaid(array $payload)
    {
        $id = data_get($payload, 'data.object.id');

        $payout = UserPayout::wherePayoutId($id)->first();

        if ($payout && data_get($payload, 'data.object.status') === Payout::STATUS_PAID) {
            $payout->update([
                'status' => data_get($payload, 'data.object.status'),
            ]);

            Mail::to($payout->user->email)->send(new SuccessPayout($payout));
        }

        return $this->successMethod();
    }

    public function handlePayoutFailed(array $payload)
    {
        $id = data_get($payload, 'data.object.id');

        if (($payout = UserPayout::wherePayoutId($id)->first())) {
            $payout->update([
                'status' => data_get($payload, 'data.object.status'),
                'failure_code' => data_get($payload, 'data.object.failure_code'),
                'failure_message' => data_get($payload, 'data.object.failure_message'),
            ]);

            Mail::to($payout->user->email)->send(new PayoutFailed($payout->fresh()));
        }

        return $this->successMethod();
    }
}
