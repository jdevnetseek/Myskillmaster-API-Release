<?php

namespace Tests\Feature\Controllers\V1\Lesson\EnrollmentController;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\MasterLesson;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StoreTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;


    // Test
    // user should be able to enroll to a lesson
    // user shout not be able to enroll to a lesson where master don't have payouts enabled yet
    // user should only be able to enroll to active lesson
    // user should only be able to enroll to available schedule of lesson
    // user should not be able to enroll with conflicting schedule

    /** @test */
    public function user_should_be_to_enroll_to_a_lesson()
    {
        $this->markTestSkipped();
        // create lesson
        $lesson = $this->createLesson();

        $student = User::factory()->create();

        // create data for enrollment

        // need dummy payment method id
        // create mock object for payment or mock api for stripe

        $this->actingAs($student)
            ->postJson($this->endpoint($lesson->getKey()), $this->payload())
            ->assertCreated()
            ->assertJsonStructure([
                'data' => $this->expectedResponseData(),
            ]);
    }

    private function createLesson()
    {
        // @todo: include schedules here after implementation
        return MasterLesson::factory()
            ->for(User::factory()->payoutsEnabled(), 'user')
            ->create();
    }

    private function payload(): array
    {
        return [
            'payment_method_id' => Str::random(10), // Stripe payment method id
            'to_learn' => $this->faker->text(500),
        ];
    }

    private function expectedResponseData(): array
    {
        return [
            'id',
            'lesson',
            'reference_code',
            'schedule_id',
            'schedule',
            'status',

            // flags
            'is_cancelled_by_student',
            'is_cancelled_by_master',

            // timestamps
            'student_cancelled_at',
            'master_cancelled_at',
            'paid_at',
            'refunded_at',
            'created_at',
            'updated_at',

            // amount data
            'lesson_price',
            'sub_total',
            'application_fee_amount',
            'application_fee_rate',
            'grand_total',
            'currency',
        ];
    }

    private function endpoint($id): string
    {
        return "api/v1/lessons/$id/enroll";
    }
}
