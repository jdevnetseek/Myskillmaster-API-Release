<?php

namespace Tests\Feature\Controllers\V1\Lesson\LessonScheduleController;

use App\Models\LessonSchedule;
use App\Models\MasterLesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->seedPlans();
    }

    /**
     * @test
     */
    public function authenticated_user_and_lesson_owner_can_create_lesson_schedule()
    {
        $user = User::factory()->create();

        $masterLesson = MasterLesson::factory()->create([
            'user_id' => $user->id,
        ]);

        // Create the request data
        $data = [
            'master_lesson_id' => $masterLesson->id,
            'timezone' => 'Asia/Manila',
            'lesson_schedules' => $this->schedulePayload(),

        ];

        $response = $this->actingAs($user)->postJson('api/v1/lesson/schedule', $data);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    "id",
                    "master_lesson_id",
                    "schedule_start",
                    "schedule_end",
                    "slots",
                    "lesson_duration",
                    "is_available_for_enrollment",
                    "number_of_students_enrolled"
                ]
            ]
        ]);

        // Assert that the lesson schedule was created successfully
        $this->assertDatabaseHas('lesson_schedules', [
            'master_lesson_id' => $masterLesson->id,
            'schedule_start' => $response->json('data.0.schedule_start'),
        ]);
    }

    private function schedulePayload()
    {
        return  [
            [
                'schedule_start' => now()->addDays(1)->format('Y-m-d H:i:s'),
                'slots' => 1,
                'duration_in_hours' => 1
            ]
        ];
    }

    /**
     * @test
     */
    public function authenticated_user_and_not_lesson_owner_cannot_create_lesson_schedule()
    {
        $user = User::factory()->create();

        $masterLesson = MasterLesson::factory()->create();

        // Create the request data
        $data = [
            'master_lesson_id' => $masterLesson->id,
            'timezone' => 'Asia/Manila',
            'lesson_schedules' => $this->schedulePayload(),
        ];

        $response = $this->actingAs($user)->postJson('api/v1/lesson/schedule', $data);

        $response->assertForbidden();
        $response->assertJson([
            'message' => 'Unauthorized. Only the owner of the lesson can create schedules.',
        ]);

        // Assert that the lesson schedule was not created
        $this->assertDatabaseMissing('lesson_schedules', [
            'master_lesson_id' => $masterLesson->id,
            'schedule_start' => $this->schedulePayload()[0]['schedule_start'],
        ]);
    }

    /**
     * @test
     */
    public function unauthenticated_user_cannot_create_lesson_schedule()
    {
        $masterLesson = MasterLesson::factory()->create();

        // Create the request data
        $data = [
            'master_lesson_id' => $masterLesson->id,
            'timezone' => 'Asia/Manila',
            'lesson_schedules' => $this->schedulePayload(),
        ];

        $response = $this->postJson('api/v1/lesson/schedule', $data);

        $response->assertUnauthorized();
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    /**
     * @test
     */
    public function validate_master_lesson_id_is_required()
    {
        $user = User::factory()->create();

        $masterLesson = MasterLesson::factory()->create([
            'user_id' => $user->id,
        ]);

        // Create the request data
        $data = [
            'lesson_schedules' => $this->schedulePayload(),
            'timezone' => 'Asia/Manila'
        ];

        $response = $this->actingAs($user)->postJson('api/v1/lesson/schedule', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['master_lesson_id']);
    }

    /**
     * @test
     */
    public function validate_master_lesson_id_must_be_an_integer()
    {
        $user = User::factory()->create();

        $masterLesson = MasterLesson::factory()->create([
            'user_id' => $user->id,
        ]);

        // Create the request data
        $data = [
            'master_lesson_id' => 'invalid',
            'lesson_schedules' => $this->schedulePayload(),
            'timezone' => 'Asia/Manila'
        ];

        $response = $this->actingAs($user)->postJson('api/v1/lesson/schedule', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['master_lesson_id']);
    }

    /**
     * @test
     */
    public function validate_master_lesson_id_must_exist_in_master_lessons_table()
    {
        $user = User::factory()->create();

        $masterLesson = MasterLesson::factory()->create([
            'user_id' => $user->id,
        ]);

        // Create the request data
        $data = [
            'master_lesson_id' => 999,
            'lesson_schedules' => $this->schedulePayload(),
            'timezone' => 'Asia/Manila'
        ];

        $response = $this->actingAs($user)->postJson('api/v1/lesson/schedule', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['master_lesson_id']);
    }

    /**
     * @test
     */
    public function validate_timezone_is_required()
    {
        $user = User::factory()->create();

        $masterLesson = MasterLesson::factory()->create([
            'user_id' => $user->id,
        ]);

        // Create the request data
        $data = [
            'master_lesson_id' => $masterLesson->id,
            'lesson_schedules' => $this->schedulePayload(),
        ];

        $response = $this->actingAs($user)->postJson('api/v1/lesson/schedule', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['timezone']);
    }

    /**
     * @test
     */
    public function validate_timezone_must_be_a_valid_timezone()
    {
        $user = User::factory()->create();

        $masterLesson = MasterLesson::factory()->create([
            'user_id' => $user->id,
        ]);

        // Create the request data
        $data = [
            'master_lesson_id' => $masterLesson->id,
            'lesson_schedules' => $this->schedulePayload(),
            'timezone' => 'invalid'
        ];

        $response = $this->actingAs($user)->postJson('api/v1/lesson/schedule', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['timezone']);
    }

    /**
     * @test
     */
    public function validate_lesson_schedules_is_required()
    {
        $user = User::factory()->create();

        $masterLesson = MasterLesson::factory()->create([
            'user_id' => $user->id,
        ]);

        // Create the request data
        $data = [
            'master_lesson_id' => $masterLesson->id,
            'timezone' => 'Asia/Manila'
        ];

        $response = $this->actingAs($user)->postJson('api/v1/lesson/schedule', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['lesson_schedules']);
    }

    /**
     * @test
     */
    public function validate_lesson_schedules_must_be_an_array()
    {
        $user = User::factory()->create();

        $masterLesson = MasterLesson::factory()->create([
            'user_id' => $user->id,
        ]);

        // Create the request data
        $data = [
            'master_lesson_id' => $masterLesson->id,
            'lesson_schedules' => 'invalid',
            'timezone' => 'Asia/Manila'
        ];

        $response = $this->actingAs($user)->postJson('api/v1/lesson/schedule', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['lesson_schedules']);
    }

    /**
     * @test
     */
    public function validate_lesson_schedules_must_have_at_least_one_schedule()
    {
        $user = User::factory()->create();

        $masterLesson = MasterLesson::factory()->create([
            'user_id' => $user->id,
        ]);

        // Create the request data
        $data = [
            'master_lesson_id' => $masterLesson->id,
            'lesson_schedules' => [],
            'timezone' => 'Asia/Manila'
        ];

        $response = $this->actingAs($user)->postJson('api/v1/lesson/schedule', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['lesson_schedules']);
    }

    /**
     * @test
     */
    public function validate_lesson_schedules_schedule_start_must_have_a_valid_date()
    {
        $user = User::factory()->create();

        $masterLesson = MasterLesson::factory()->create([
            'user_id' => $user->id,
        ]);

        // Create the request data
        $data = [
            'master_lesson_id' => $masterLesson->id,
            'lesson_schedules' => [
                [
                    'schedule_start' => 'invalid',
                    'duration_in_hours' => 1,
                ]
            ],
            'timezone' => 'Asia/Manila'
        ];

        $response = $this->actingAs($user)->postJson('api/v1/lesson/schedule', $data);
        $response->dump();

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['lesson_schedules.0.schedule_start']);
    }

    /**
     * @test
     */
    public function validate_lesson_schedules_duration_in_hours_is_required_field()
    {
        $user = User::factory()->create();

        $masterLesson = MasterLesson::factory()->create([
            'user_id' => $user->id,
        ]);

        $tomorrow = Carbon::now()->addDay()->format('Y-m-d H:i:s');

        // Create the request data
        $data = [
            'master_lesson_id' => $masterLesson->id,
            'lesson_schedules' => [
                [
                    'schedule_start' => $tomorrow,
                ]
            ],
            'timezone' => 'Asia/Manila'
        ];

        $response = $this->actingAs($user)->postJson('api/v1/lesson/schedule', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['lesson_schedules.0.duration_in_hours']);
    }

    /**
     * @test
     */
    public function validate_lesson_schedules_duration_in_hours_must_be_an_integer()
    {
        $user = User::factory()->create();

        $masterLesson = MasterLesson::factory()->create([
            'user_id' => $user->id,
        ]);

        $tomorrow = Carbon::now()->addDay()->format('Y-m-d H:i:s');

        // Create the request data
        $data = [
            'master_lesson_id' => $masterLesson->id,
            'lesson_schedules' => [
                [
                    'schedule_start' => $tomorrow,
                    'duration_in_hours' => 'invalid',
                ]
            ],
            'timezone' => 'Asia/Manila'
        ];

        $response = $this->actingAs($user)->postJson('api/v1/lesson/schedule', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['lesson_schedules.0.duration_in_hours']);
    }

    /**
     * @test
     */
    public function validate_lesson_schedules_duration_in_hours_must_be_greater_than_zero()
    {
        $user = User::factory()->create();

        $masterLesson = MasterLesson::factory()->create([
            'user_id' => $user->id,
        ]);

        $tomorrow = Carbon::now()->addDay()->format('Y-m-d H:i:s');

        // Create the request data
        $data = [
            'master_lesson_id' => $masterLesson->id,
            'lesson_schedules' => [
                [
                    'schedule_start' => $tomorrow,
                    'duration_in_hours' => 0,
                ]
            ],
            'timezone' => 'Asia/Manila'
        ];

        $response = $this->actingAs($user)->postJson('api/v1/lesson/schedule', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['lesson_schedules.0.duration_in_hours']);
    }

    /**
     * @test
     */
    public function user_should_not_be_able_to_proceed_on_creating_lesson_schedules_if_it_has_duplicate_schedule()
    {
        $user = User::factory()->create();

        $masterLesson = MasterLesson::factory()->create([
            'user_id' => $user->id,
        ]);

        $tomorrow = Carbon::now()->addDay()->format('Y-m-d H:i:s');

        // Create the request data
        $data = [
            'master_lesson_id' => $masterLesson->id,
            'lesson_schedules' => [
                [
                    'schedule_start' => $tomorrow,
                    'duration_in_hours' => 1,
                ],
                [
                    'schedule_start' => $tomorrow,
                    'duration_in_hours' => 1,
                ]
            ],
            'timezone' => 'Asia/Manila'
        ];

        $response = $this->actingAs($user)->postJson('api/v1/lesson/schedule', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['lesson_schedules']);
    }

    private function seedPlans()
    {
        $this->artisan('db:seed', ['--class' => 'CountriesTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'PlacesTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'PlanTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'CategoriesTableSeeder']);
    }
}
