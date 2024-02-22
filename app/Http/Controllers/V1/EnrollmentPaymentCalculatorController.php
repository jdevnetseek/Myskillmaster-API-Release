<?php

namespace App\Http\Controllers\V1;

use App\Models\MasterLesson;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Enrollment\Objects\ApplicationFee;
use App\Services\Enrollment\Objects\PaymentDetails;
use App\Services\Enrollment\LessonPaymentCalculator;

class EnrollmentPaymentCalculatorController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'lesson_id' => ['required'],
        ]);

        $lesson = MasterLesson::findOrFail($request->lesson_id);

        /** @var PaymentDetails */
        $paymentDetails = resolve(LessonPaymentCalculator::class, [
            'price' => $lesson->lesson_price,
        ])->execute();

        /** @var ApplicationFee */
        $applicationFee = $paymentDetails->applicationFee;

        return response()->json([
            'data' => [
                'price' => $paymentDetails->price,
                'sub_total' => $paymentDetails->subTotal,
                'admin_fee' => $applicationFee->adminFee,
                'grand_total' => $paymentDetails->grandTotal,

                'application_fee' => [
                    'amount' => $applicationFee->amount,
                    'rate' => $applicationFee->rate,
                ],

                'master_earnings' => $paymentDetails->masterEarnings,

                'currency' => $paymentDetails->currency,
            ],
        ]);
    }
}
