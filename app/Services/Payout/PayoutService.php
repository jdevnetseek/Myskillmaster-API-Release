<?php

namespace App\Services\Payout;

use App\Models\LessonEnrollment;
use Carbon\Carbon;
use Stripe\Payout;
use App\Models\User;
use Stripe\StripeClient;
use App\Models\UserPayout;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Cashier\Cashier;
use App\Services\Objects\Money;
use App\Services\Payout\Exceptions\HasOngoingReportException;
use App\Services\Payout\Exceptions\InvalidPayoutAmountException;
use App\Services\Payout\Exceptions\NoAvailableBalanceForPayoutException;
use Illuminate\Support\Collection;

class PayoutService
{
    private StripeClient $stripeClient;

    private ?int $payoutAmountInCents = null;

    private string $destinationId = '';

    private string $description = '';

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

    public function setDestination(string $destinationId): self
    {
        $this->destinationId = $destinationId;
        return $this;
    }

    public function setPayoutAmountInCents(int $payoutAmountInCents): self
    {
        $this->payoutAmountInCents = $payoutAmountInCents;
        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @throws App\Services\Payout\Exceptions\NoAvailableBalanceForPayoutException
     * @throws App\Services\Payout\Exceptions\InvalidPayoutAmountException
     * @throws App\Services\Payout\Exceptions\HasOngoingReportException
     */
    public function create(): UserPayout
    {
        $this->assertThatUserCanCreatePayout();

        /** @var Money */
        $maxPayableAmount = $this->payableAmount();

        $this->payoutAmountInCents = $this->payoutAmountInCents ?? $maxPayableAmount->inCents();

        if ($maxPayableAmount->inCents() <= 0) {
            throw new NoAvailableBalanceForPayoutException;
        }

        if ($this->payoutAmountInCents == 0) {
            throw new InvalidPayoutAmountException('Amount should be greater than 0');
        }

        if ($this->payoutAmountInCents > $maxPayableAmount->inCents()) {
            throw new InvalidPayoutAmountException('Amount should not exceed the available balance.');
        }

        /**
         * Statement descriptor must have 22 characters at most
         * @see https://stripe.com/docs/api/payouts/create
         */
        $statementDescriptor = Str::limit(config('app.name') . ' payout', 22, '');

        $payload = [
            'amount' => $this->payoutAmountInCents,
            'currency' => $maxPayableAmount->currency,
            'description' => empty($this->description) ? 'master requested payout' : $this->description,
            'statement_descriptor' => $statementDescriptor,
            'metadata' => [
                'user_id' => $this->user->getKey(),
            ],
        ];

        if (!empty($this->destinationId)) {
            $payload['destination'] = $this->destinationId;
        }

        /** @var \Stripe\Payout  */
        $payoutObject = $this->stripeClient->payouts->create($payload);

        $payout = $this->storePayoutRecord($payoutObject);

        return $payout;
    }

    protected function storePayoutRecord(Payout $stripePayout): UserPayout
    {
        $payout = $this->user->payouts()->create([
            'payout_id' => $stripePayout->id,
            'amount' => $stripePayout->amount / 100,
            'currency' => $stripePayout->currency,
            'status' => $stripePayout->status,
            'is_initiated_by_user' => true,
            'arrival_date' => Carbon::createFromTimestamp($stripePayout->arrival_date),
        ]);

        return $payout;
    }

    public function payableAmount(): Money
    {
        $balance = resolve(BalanceService::class, ['user' => $this->user])->get();

        $available = data_get($balance, 'available');

        return new Money($available['amount'], $available['currency']);
    }

    private function assertThatUserCanCreatePayout()
    {
        if ($this->user->payouts_enabled == false) {
            throw new \Exception('The payout is disabled for this user');
        }

        if ($this->user->isReportedLessonsResolved() == false) {
            throw new HasOngoingReportException;
        }
    }
}
