<?php

namespace App\Mail\Subscription;

use Laravel\Cashier\Subscription;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class SubscriptionCancelConfirmation extends Mailable  implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $user;
    protected $subscription;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, Subscription $subscription)
    {
        $this->user = $user;
        $this->subscription = $subscription;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.subscription.cancel-confirmation')
            ->subject(config('app.name') . ' - Your Subscription Has Been Cancelled')
            ->with([
                'plan_name' => optional($this->subscription)->name,
                'plan_ends_at' => $this->cancelledAt(),
                'name' => $this->user->full_name ? $this->user->full_name : 'Customer'
            ]);
    }

    private function cancelledAt()
    {
        $date = Carbon::parse(optional($this->subscription)->ends_at);
        $formattedDate = $date->format('F j, Y');

        return $formattedDate;
    }
}
