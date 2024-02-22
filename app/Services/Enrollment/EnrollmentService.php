<?php

namespace App\Services\Enrollment;

use PaymentSuccess;
use App\Models\User;
use Stripe\ErrorObject as StripeErrorObject;
use Stripe\PaymentIntent;
use App\Models\MasterLesson;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Payment;
use App\Models\LessonSchedule;
use App\Models\LessonEnrollment;
use App\Models\EnrollmentPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\Enrollment\Objects\ApplicationFee;
use App\Services\Enrollment\Objects\PaymentDetails;
use Stripe\Exception\CardException as StripeCardException;
use App\Services\Enrollment\Exceptions\InvalidCardException;
use App\Services\Enrollment\Exceptions\InvalidScheduleException;
use App\Services\Enrollment\Exceptions\ConflictScheduleException;
use App\Services\Enrollment\Exceptions\NoSlotsAvailableException;

class EnrollmentService
{
    public function __construct(
        protected MasterLesson $lesson,
        protected User $student,
        protected string $studentObjective = ''
    ) {
    }

    public function setStudentObjective(string $objective): self
    {
        $this->studentObjective = $objective;
        return $this;
    }

    /**
     * @throws App\Services\Enrollment\Exceptions\InvalidScheduleException
     * @throws App\Services\Enrollment\Exceptions\ConflictScheduleException
     * @throws App\Services\Enrollment\Exceptions\NoSlotsAvailableException
     * @throws Laravel\Cashier\Exceptions\InvalidPaymentMethod
     * @throws Stripe\Exception\ApiErrorException
     */
    public function enroll(LessonSchedule $schedule, string $paymentMethod): LessonEnrollment
    {
        // extras -
        // atomic operation to prevent double booking
        //  - use atomic lock
        //  - use schedule id for lock

        $this->ensureThatScheduleIsValid($schedule);

        $this->ensureThatStudentHasNoConflictingSchedule($schedule);

        $this->ensureThatPaymentMethodIsValid($paymentMethod);

        [$lessonEnrollment, $enrollmentPayment] = DB::transaction(function () use ($paymentMethod, $schedule) {

            $lessonEnrollment = $this->createLessonEnrollment($schedule);

            /** @var Payment */
            $payment = $this->pay($lessonEnrollment, $paymentMethod);

            if ($payment->isSucceeded()) {
                $lessonEnrollment->update([
                    'paid_at' => now(),
                ]);
            }

            $enrollmentPayment = $this->storePayment($lessonEnrollment, $payment);

            return [$lessonEnrollment, $enrollmentPayment];
        });

        event(new PaymentSuccess($enrollmentPayment));

        return $lessonEnrollment;
    }

    private function createLessonEnrollment(LessonSchedule $schedule): LessonEnrollment
    {
        /** @var PaymentDetails */
        $paymentDetails = $this->calculatePayment($this->lesson);

        /** @var ApplicationFee */
        $applicationFee = $paymentDetails->applicationFee;

        // create  enrollment record
        return $this->lesson->enrollments()->create([
            'schedule_id' => $schedule->getKey(),
            'student_id' => $this->student->getKey(),
            'master_id' => $this->lesson->user->getKey(),

            'to_learn' => $this->studentObjective,

            // price
            'lesson_price' => $this->lesson->lesson_price,
            'admin_fee' => $applicationFee->adminFee,
            'sub_total' => $paymentDetails->subTotal,
            'grand_total' => $paymentDetails->grandTotal,

            'application_fee_amount' => $applicationFee->amount,
            'application_fee_rate' => $applicationFee->rate,
            'total_fee' => $applicationFee->totalFee,
            'master_earnings' => $paymentDetails->masterEarnings,

            'currency' => $paymentDetails->currency,
        ]);
    }

    protected function ensureThatScheduleIsValid(LessonSchedule $schedule): void
    {
        if ($this->lesson->getKey() != $schedule->master_lesson_id) {
            throw new InvalidScheduleException('Invalid schedule. Schedule does not belong to the lesson');
        }

        if (now()->greaterThan($schedule->start_date)) {
            throw new InvalidScheduleException('Invalid schedule. Schedule must be a future date');
        }

        if ($schedule->hasAvailableSlotsForEnrollment() !== true) {
            throw new NoSlotsAvailableException;
        }
    }

    /**
     * @throws \Laravel\Cashier\Exceptions\InvalidPaymentMethod
     */
    protected function ensureThatPaymentMethodIsValid(string $paymentMethod)
    {
        $this->student->findPaymentMethod($paymentMethod);
    }

    /** @throws ConflictScheduleException */
    public function ensureThatStudentHasNoConflictingSchedule(LessonSchedule $schedule): void
    {
        $exists = LessonEnrollment::userAsMasterOrStudent($this->student)
            ->notCancelled()
            ->whereHas('schedule', function ($query) use ($schedule) {
                $dows = explode(', ', $schedule->dows);

                $query->where(function ($query) use ($dows) {
                    foreach ($dows as $dow) {
                        $query->orWhereRaw("find_in_set('{$dow}', dows)");
                    }
                })
                    ->where(function ($query) use ($schedule) {
                        $query->whereRaw("Date(schedule_start) <= DATE('{$schedule->schedule_end}')")
                            ->WhereRaw("Date(schedule_end) >= DATE('{$schedule->schedule_start}')")
                            ->whereRaw("Time(schedule_start) < TIME('{$schedule->schedule_end}') ")
                            ->whereRaw("Time(schedule_end) > TIME('{$schedule->schedule_start}') ");
                    });
            })
            ->exists();

        if ($exists) {
            throw new ConflictScheduleException;
        }
    }

    private function calculatePayment($lesson): PaymentDetails
    {
        /** @var PaymentDetails */
        $paymentDetails = resolve(LessonPaymentCalculator::class, [
            'price' => $lesson->lesson_price,
        ])->execute();

        return $paymentDetails;
    }

    // return stripe payment object
    /**
     * Handles payment to Stripe
     *
     * @throws Stripe\Exception\ApiErrorException
     * @throws Stripe\Exception\CardException
     */
    private function pay(LessonEnrollment $enrollment, string $paymentMethod)
    {
        $totalInCents = $enrollment->grand_total * 100;
        $applicationFeeInCents = ($enrollment->total_fee) * 100;

        $payment = [
            'amount'                    => $totalInCents,
            'currency'                  => $enrollment->currency,
            'customer'                  => $this->student->stripeId(),
            'receipt_email'             => $this->student->email,
            'payment_method'            => $paymentMethod,
            'application_fee_amount'    => $applicationFeeInCents,
            'on_behalf_of'              => $enrollment->master->stripeConnectId(),
            'transfer_data' => [
                'destination' => $enrollment->master->stripeConnectId(),
            ],
            'confirm'   => true,
            'metadata' => [
                'enrollment_id' => $enrollment->getKey(),
                'reference_code' => $enrollment->reference_code,
                'lesson_id' => $enrollment->lesson_id,
                'student_id' => $enrollment->student_id,
            ]
        ];

        try {
            $stripePayment = new Payment(PaymentIntent::create($payment, Cashier::stripeOptions()));

            return $stripePayment;
        } catch (StripeCardException $e) {
            $this->handleStripeCardErrorException($e);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function storePayment(LessonEnrollment $enrollment, Payment $stripePayment): EnrollmentPayment
    {
        return $enrollment->payments()->create([
            'payment_id' => $stripePayment->id,
            'amount' => $stripePayment->rawAmount() / 100, // stripe will return amount in cents
            'type' => 'enrollment',
            'paid_at' => $stripePayment->isSucceeded() ? now() : null,
        ]);
    }

    /**
     * @throws InvalidCardException
     */
    private function handleStripeCardErrorException(StripeCardException $exception)
    {
        $message = $exception->getMessage();

        if ($exception->getStripeCode() === StripeErrorObject::CODE_CARD_DECLINED) {
            /**
             * @see https://stripe.com/docs/declines/codes
             */
            switch ($exception->getDeclineCode()) {
                case 'fraudulent':
                case 'lost_card':
                case 'stolen_card':
                case 'merchant_blacklist':
                    $message = 'The card was declined for an unknown reason';
                    break;
            }
        }

        throw new InvalidCardException($message);
    }

    private function generateCacheKeyUsingSchedule(LessonSchedule $schedule): string
    {
        return $schedule->getKey() . '-' . $schedule->start_date;
    }
}
