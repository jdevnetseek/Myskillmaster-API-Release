<?php

namespace App\Mail\Payout;

use App\Models\UserPayout;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Laravel\Cashier\Cashier;
use Stripe\Payout;

class PayoutFailed extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

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
        return $this->markdown('emails.payout.failed-payout')
            ->subject(config('app.name') . ' - Payout Failed')
            ->with([
                'payout' => $this->payout,
                'date_requested' => $this->formattedCreatedDate(),
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
        $timezone = 'AEST';

        return $this->payout->created_at->setTimezone($timezone)->toDayDateTimeString()
            . " ($timezone)";
    }
}
