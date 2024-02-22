<?php

use App\Models\EnrollmentPayment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentSuccess
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly EnrollmentPayment $enrollmentPayment)
    {}
}
