<?php

namespace App\Http\Controllers\V1\Auth\Connect;

use Stripe\Payout;
use App\Models\User;
use App\Models\UserPayout;
use App\Models\LessonEnrollment;
use App\Http\Controllers\Controller;
use App\Services\Payout\PayoutService;
use App\Exceptions\IncompleteStripeConnectPayout;
use App\Services\Payout\Exceptions\HasOngoingReportException;
use App\Services\Payout\Exceptions\InvalidPayoutAmountException;
use App\Services\Payout\Exceptions\NoAvailableBalanceForPayoutException;
use App\Exceptions\Payout\HasOngoingReportException as HttpHasOngoingReportException;
use App\Exceptions\Payout\InvalidPayoutAmountException as HttpInvalidPayoutAmountException;
use App\Exceptions\Payout\NoAvailableBalanceForPayoutException as HttpNoAvailableBalanceForPayoutException;
use App\Services\Payout\BalanceService;

class RequestPayoutController extends Controller
{
    public function __invoke()
    {
        try {

            $user = request()->user();

            throw_if($user->payouts_enabled == false, IncompleteStripeConnectPayout::class);

            /** @var  \App\Models\UserPayout */
            $userPayout = resolve(PayoutService::class, ['user' => $user])->create();

            $this->associatePayoutToEnrollments($userPayout, $user);

            $data = $this->retreivePayoutBalance($user);
            $data['payout_amount'] = [
                'amount' => $userPayout->amount,
                'currency' => $userPayout->currency,
            ];

            // return current balance?
            return response()->json([
                'data' => $data,
            ]);

        } catch (NoAvailableBalanceForPayoutException $e) {
            throw new HttpNoAvailableBalanceForPayoutException;
        } catch (InvalidPayoutAmountException $e) {
            throw new HttpInvalidPayoutAmountException($e->getMessage());
        } catch (HasOngoingReportException $e) {
            throw new HttpHasOngoingReportException;
        }
    }

    protected function associatePayoutToEnrollments(UserPayout $payout, $user)
    {
        // get all enrollments that are paid and has no payout
        $enrollments = LessonEnrollment::asMaster($user)
            ->whereNull('refunded_at')
            ->with('payouts')
            ->paid()
            ->get();

        /**
         * We only want to payout enrollments that available balance can cover
         * For example:
         *    - available balance: $20
         *    - enrollments without associated payout:
         *      - enrollment A - $7
         *      - enrollment B - $10
         *      - enrollment C - $4
         *      - enrollment D - $3
         *    - total earnings = $24
         *    - available balance = $20
         *    - we will only associate enrollment A($7), B($10), D($3) = $20
         *      - because that enrollments (A, B, D) can be covered by the available balance
         */

        $accumulatedEarnings = 0;
        $enrollmentIdsToBePayout = [];

        $enrollments->each(function ($e) use (&$accumulatedEarnings, $payout, &$enrollmentIdsToBePayout) {
            if ($accumulatedEarnings >= $payout->amount) {
                return;
            }

            /** @var \App\Models\UserPayout */
            $latestPayout = $e->payouts->first();

            if (! is_null($latestPayout) && $latestPayout->status !== Payout::STATUS_FAILED) {
                return;
            }

            if ($accumulatedEarnings + $e->master_earnings <= $payout->amount) {
                $accumulatedEarnings += $e->master_earnings;
                $enrollmentIdsToBePayout[] = $e->getKey();
            }
        });

        $payout->paidOutEnrollments()->sync($enrollmentIdsToBePayout);
    }

    protected function retreivePayoutBalance(User $user): array
    {
        return resolve(BalanceService::class, ['user' => $user])->get();
    }
}
