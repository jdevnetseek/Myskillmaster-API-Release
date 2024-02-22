<?php

namespace App\Mail\Payout;

use App\Models\UserPayout;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Laravel\Cashier\Cashier;
use Stripe\Payout;

class SuccessPayout extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $timezone = 'AEST';

    public function __construct(public UserPayout $payout)
    {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.payout.success-payout')
            ->subject(config('app.name') . ' - Payout Success')
            ->with([
                'payout' => $this->payout,
                'date_requested' => $this->formattedCreatedDate(),
                'arrival_date' => $this->formattedArrivalDate(),
                'bank' => $this->retrieveDestinationDetails(),
            ]);
    }

    private function retrieveDestinationDetails()
    {
        $stripePayout = Payout::retrieve(
            [
                'id' => $this->payout->payout_id,
                'expand' => ['destination'],
            ],
            Cashier::stripeOptions([
                'stripe_account' => $this->payout->user->stripeConnectId(),
            ])
        );

        return [
            'name' => $stripePayout->destination->bank_name,
            'last4' => $stripePayout->destination->last4,
        ];
    }

    private function formattedCreatedDate(): string
    {
        return $this->payout->created_at->setTimezone($this->timezone)->toDayDateTimeString()
            . " ($this->timezone)";
    }

    private function formattedArrivalDate(): string
    {
        return $this->payout->arrival_date->setTimezone($this->timezone)
            ->toFormattedDateString();
    }
}
