<?php

namespace App\Jobs\Stripe\Payout;

use Stripe\Payout;
use App\Models\User;
use Illuminate\Bus\Queueable;
use App\Services\Payout\PayoutService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\Payout\Exceptions\HasOngoingReportException;
use App\Services\Payout\Exceptions\InvalidPayoutAmountException;
use App\Services\Payout\Exceptions\NoAvailableBalanceForPayoutException;

class SendPayout implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private int $scheduledPayoutInDays = 30)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /**
         * scenario when user does not trigger any payouts
         *  May 15
         *   - $10 - Transaction A
         *   - $5 - Transcation B
         *  May 28
         *   - $5
         *   - $3
         *
         *  June 14 -
         *   - May 15 transactions
         *      - request payout for this transactions
         */

         $users = User::withWhereHas('enrollmentsAsMaster', function ($query) {
            $query->paid()
                ->whereNull('refunded_at')
                ->whereRaw('DATEDIFF(?, `paid_at`) >= ?', [now(), $this->scheduledPayoutInDays])
                ->with('payouts');
        })->where('payouts_enabled', true);

        $users->chunk(100, function ($users) {
            $users->each(function ($user) {
                try {
                    // Only include enrollments without payouts
                    // or a payout with failed status
                    $enrollmentsToBePayout = $user->enrollmentsAsMaster
                        ->filter(function ($enrollment) {
                            $latestPayout = $enrollment->payouts->first();

                            return is_null($latestPayout)
                                || $latestPayout->status === Payout::STATUS_FAILED;
                        });

                    $payoutAmountInCents = $enrollmentsToBePayout->sum('master_earnings') * 100;

                    $payout = resolve(PayoutService::class, ['user' => $user])
                        ->setDescription('myskillmaster scheduled payout')
                        ->setPayoutAmountInCents($payoutAmountInCents)
                        ->create();

                    $payout->paidOutEnrollments()->sync($enrollmentsToBePayout);

                } catch (HasOngoingReportException
                    |InvalidPayoutAmountException
                    |NoAvailableBalanceForPayoutException $e
                ) {
                    logger()->channel('scheduled_payout_log')->info('user: ' . $user->email);
                    logger()->channel('scheduled_payout_log')->error($e->getMessage());
                }
            });
        });
    }
}
