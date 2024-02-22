<?php

namespace App\Mail\Subscription;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionUpdateConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $user;
    protected $plan;
    protected $oldPlanName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Plan $plan, User $user, ?string $oldPlanName = null)
    {
        $this->plan = $plan;
        $this->user = $user;
        $this->oldPlanName = $oldPlanName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.subscription.update-confirmation')
            ->subject(config('app.name') . ' - Subscription Update Confirmation')
            ->with([
                'old_plan_name' => $this->oldPlanName,
                'name' => $this->user->full_name ? $this->user->full_name : 'Customer',
                'plan_name' => $this->plan->name,
                'plan_price' => $this->planPrice(),
                'currency' => $this->getCurrency()
            ]);
    }

    private function planPrice()
    {
        $price = $this->plan->price;
        $price = $price / 100;
        $price = number_format($price, 2, '.', '');
        return $price;
    }

    private function getCurrency()
    {
        $lastInvoice = $this->user->invoices()->last();

        return $lastInvoice->asStripeInvoice()->currency;
    }
}
