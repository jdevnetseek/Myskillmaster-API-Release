<?php

namespace App\Jobs\Stripe;

use App\Models\PaymentHistory;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ChargeSucceededJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $charge;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($charge)
    {
        $this->charge = $charge;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::where('stripe_id', $this->charge['customer'])->first();

        if ($user) {
            PaymentHistory::create([
                'user_id'  => $user->id,
                'stripe_id' => $this->charge['id'],
                'subtotal' => $this->charge['amount'],
                'total'    => $this->charge['amount'],
            ]);
        }
    }
}
