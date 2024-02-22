<?php

namespace App\Services\Enrollment;

use Stripe\Refund;
use Stripe\Stripe;
use App\Models\User;
use Stripe\StripeClient;
use Laravel\Cashier\Cashier;
use App\Models\LessonSchedule;
use App\Models\LessonEnrollment;
use App\Models\EnrollmentPayment;
use Illuminate\Support\Facades\DB;
use App\Events\LessonEnrollment\LessonCancelled;
use App\Services\Enrollment\Exceptions\InvalidMasterException;
use App\Services\Enrollment\Exceptions\InvalidStudentException;
use App\Services\Enrollment\Exceptions\LessonCancellationException;

class CancelEnrollmentService
{
    protected $cancellationReason = null;
    protected $cancellationRemarks = null;

    public function __construct(protected User $user)
    {
    }

    public function setCancellationReason(?string $reason): self
    {
        $this->cancellationReason = $reason;
        return $this;
    }

    public function setCancellationRemarks(?string $remarks): self
    {
        $this->cancellationRemarks = $remarks;
        return $this;
    }

    /**
     * @throws App\Services\Enrollment\Exceptions\InvalidMasterException
     * @throws App\Services\Enrollment\Exceptions\LessonCancellationException
     */
    public function masterBulkCancellationBySchedule(LessonSchedule $schedule)
    {
        if ($this->user->getKey() !== $schedule->masterLesson->user_id) {
            throw new InvalidMasterException;
        }

        if ($schedule->isOngoing()) {
            throw new LessonCancellationException('Lesson is already ongoing');
        } elseif ($schedule->isCompleted()) {
            throw new LessonCancellationException('Lesson has already concluded.');
        }

        $enrollments = $schedule->lessonEnrollments()
            ->asMaster($this->user)
            ->get();

        $enrollments->each(function ($enrollment) {
            try {

                DB::beginTransaction();

                $this->cancel($enrollment, isCancelledByStudent: false);

                DB::commit();
            } catch (LessonCancellationException $e) {
                DB::rollback();
                return;
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        });
    }

    /**
     * @throws App\Services\Enrollment\Exceptions\InvalidStudentException
     * @throws App\Services\Enrollment\Exceptions\LessonCancellationException
     */
    public function cancelByStudent(LessonEnrollment $enrollment): void
    {
        if ($enrollment->student_id !== $this->user->getKey()) {
            throw new InvalidStudentException;
        }

        $this->assertThatLessonEnrollmentIsCancellable($enrollment);

        DB::transaction(function () use ($enrollment) {
            $this->cancel($enrollment, isCancelledByStudent: true);
        });
    }

    protected function cancel(LessonEnrollment $enrollment, bool $isCancelledByStudent)
    {
        $this->assertThatLessonEnrollmentIsCancellable($enrollment);

        $data = [
            'cancellation_reason' => $this->cancellationReason,
            'cancellation_remarks' => $this->cancellationRemarks,
        ];

        if ($isCancelledByStudent) {
            $data['student_cancelled_at'] = now();
        } else {
            $data['master_cancelled_at'] = now();
        }

        $enrollment->update($data);

        // if canceled by master, we need to refund them regardless
        if (($isCancelledByStudent && $enrollment->isRefundable())
            || $enrollment->isCancelledByMaster()
        ) {
            $this->refundStudent($enrollment);
        }

        event(new LessonCancelled($enrollment, $this->user));
    }

    /**
     * @throws App\Services\Enrollment\Exceptions\LessonCancellationException
     */
    protected function assertThatLessonEnrollmentIsCancellable(LessonEnrollment $enrollment)
    {
        if ($enrollment->isCancelledByStudent()) {
            throw new LessonCancellationException('Lesson was cancelled by student');
        } elseif ($enrollment->isCancelledByMaster()) {
            throw new LessonCancellationException('Lesson was cancelled by master');
        } elseif ($enrollment->schedule->isOngoing()) {
            throw new LessonCancellationException('Lesson is already ongoing');
        } elseif ($enrollment->schedule->isCompleted()) {
            throw new LessonCancellationException('Lesson has already concluded.');
        }
    }

    protected function refundStudent(LessonEnrollment $enrollment)
    {
        /** @var \Stripe\Service\RefundService */
        $stripeRefund = (new StripeClient(Cashier::stripeOptions()))->refunds;

        $payment = $enrollment->payments()->type('enrollment')->latest()->first();
        $amountToBeRefundedInCents = ($payment->amount) * 100;

        $refund = $stripeRefund->create([
            'payment_intent' => $payment->payment_id,
            'amount' => $amountToBeRefundedInCents,
            'reverse_transfer' => true,
            // @see https://stripe.com/docs/api/refunds/create for more details for reason values
            'reason' => Refund::REASON_REQUESTED_BY_CUSTOMER,
            'metadata' => [
                'reference_code' => $enrollment->reference_code,
                'student_id' => $enrollment->student_id
            ],
        ]);

        $enrollment->update([
            'refunded_at' => now(),
        ]);

        // handle failed refund
        // email the reason and how will the customer refund it

        $enrollmentRefundPayment = new EnrollmentPayment([
            'payment_id' => $refund->id,
            'type' => 'refund',
            'amount' => $refund->amount / 100,
            'currency' => $refund->currency,
        ]);

        $enrollment->payments()->save($enrollmentRefundPayment);
    }
}
