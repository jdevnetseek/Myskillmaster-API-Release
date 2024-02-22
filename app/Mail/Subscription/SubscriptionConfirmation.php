<?php

namespace App\Mail\Subscription;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $user;
    protected $plan;
    protected $invoice;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, Plan $plan, $invoice = null)
    {
        $this->user = $user;
        $this->plan = $plan;
        $this->invoice = $invoice;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.subscription.subscription-confirmation')
            ->subject(config('app.name') . ' - Subscription Confirmation')
            ->with([
                'name' => $this->user->full_name ? $this->user->full_name : 'Customer',
                'plan_name' => $this->plan->name,
                'plan_price' => $this->planPrice(),
                'currency' => strtoupper(data_get($this->invoice['lines']['data'], '0.currency')),
                'next_billing_period' => $this->nextBillingPeriod(),
                'hosted_invoice_url' => data_get($this->invoice, 'hosted_invoice_url')
            ]);
    }

    private function planPrice()
    {
        $price = $this->plan->price;
        $price = $price / 100;
        $price = number_format($price, 2, '.', '');
        return $price;
    }

    private function nextBillingPeriod()
    {
        $upcomingInvoice = $this->user->upcomingInvoice();

        $nextInvoiceDate = $upcomingInvoice->date()->toFormattedDateString();

        return $nextInvoiceDate;
    }
}
