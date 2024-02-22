<?php

namespace Tests\Feature\Controllers\V1\Lesson\MasterLessonController;

use Illuminate\Http\UploadedFile;
use App\Enums\ErrorCodes;
use App\Models\Category;
use App\Models\MasterLesson;
use App\Models\Place;
use App\Models\Plan;
use App\Models\User;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public $placeID;
    public $randomBetween;
    public $categoryID;
    public $schedules;
    public $tags;

    public function setUp(): void
    {
        parent::setUp();

        $this->seedPlans();

        $place = Place::inRandomOrder()->first();
        $category = Category::inRandomOrder()->first();

        $this->placeID       = $place->id;
        $this->categoryID    = $category->id;
        $this->randomBetween = $this->faker()->numberBetween(1, 12);
        $this->schedules =  [
            [
                'schedule_start' => now()->addDays(1)->format('Y-m-d H:i:s'),
                'slots' => 1,
                'duration_in_hours' => 1
            ],
            [
                'schedule_start' => now()->addDays(2)->format('Y-m-d H:i:s'),
                'slots' => 2,
                'duration_in_hours' => 2
            ],
        ];
        $this->tags =  $this->faker()->randomElements([
            'Rock',
            'Rap',
            'Slow Rock'
        ]);
    }

    /**
     * @test
     */
    public function unauthenticated_master_cannot_create_lesson()
    {
        $apiUrl = "api/v1/lessons";

        $this->postJson($apiUrl, $this->payload())->assertUnauthorized();
    }

    /** @test */
    public function authenticated_master_cannot_create_lesson_without_subscription()
    {
        // Create a user without an active subscription
        $user = User::factory()->create();

        $apiUrl = "api/v1/lessons";

        $response = $this->actingAs($user)->postJson($apiUrl, $this->payload());

        $response->assertStatus(400);
        $response->assertJson([
            'error_code' => ErrorCodes::MASTER_LESSON_ERROR,
            'message' => 'You need to subscribe to one of our master plans to access exclusive features and take your experience to the next level.'
        ]);

        // Assert that the lesson was not created
        $this->assertDatabaseMissing('master_lessons', [
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function authenticated_master_cannot_create_lesson_more_than_subscription_limit()
    {
        $user   = User::factory()->create();
        $places = Place::inRandomOrder()->first();
        $plan   = Plan::where('slug', 'basic-plan')->first();

        //Arrange Create Free Plan With Subscription Limit 3 Lessons
        (new SubscriptionService)->createSubscription($user, $plan, 'tok_visa_debit', $user->full_name);

        // Attempt to create a lesson for the user
        $lesson = MasterLesson::factory(20)->create([
            'user_id' => $user->id,
            'place_id' => $places->id,
        ]);

        $apiUrl = "api/v1/lessons";
        $response = $this->actingAs($user)->postJson($apiUrl, $this->payload());

        $response->assertStatus(400);
        $response->assertJson([
            'error_code' => ErrorCodes::MASTER_LESSON_ERROR,
            "message" => "You have reached the maximum number of lessons for your current plan. Please upgrade your plan to continue using the service."
        ]);

        $this->assertDatabaseMissing('master_lessons', [
            'user_id' => $user->id,
            'title' => 'Test Lesson',
        ]);
    }

    /** @test */
    public function authenticated_master_cannot_create_lesson_when_lesson_schedule_overlap_with_each_other()
    {
        $apiUrl = "api/v1/lessons";
        $user   = User::factory()->create();
        $plan   = Plan::where('slug', 'basic-plan')->first();

        //Arrange Create Free Plan With Subscription Limit 3 Lessons
        (new SubscriptionService)->createSubscription($user, $plan, 'tok_visa_debit', $user->full_name);

        $response = $this->actingAs($user)->postJson($apiUrl, [
            'title'             => 'Test Lesson',
            'description'       => 'This is a test lesson.',
            'duration_in_hours' => $this->randomBetween,
            'lesson_price'      => $this->randomBetween,
            'category_id'       => $this->categoryID,
            'place_id'          => $this->placeID,
            'lesson_schedules'    => [
                [
                    'schedule_start' => now()->addDays(1)->format('Y-m-d H:i:s'),
                    'slots' => 1,
                    'duration_in_hours' => 1
                ],
                [
                    'schedule_start' => now()->addDays(1)->format('Y-m-d H:i:s'),
                    'slots' => 2,
                    'duration_in_hours' => 2
                ],
            ],
            'is_remote_supported'   => true,
            'tags'                  => $this->tags,
            'cover_photo' => [
                UploadedFile::fake()->image('photo1.png'),
                UploadedFile::fake()->image('photo2.png'),
            ],

            'suburb' => 'Test Suburb',
            'state' => 'Test State',
            'postcode' => 'Test Postcode',

            'timezone' => 'Australia/Sydney',
            'address_or_link' => 'https://www.google.com',
        ])
            ->assertStatus(422)
            ->assertJson([
                'message' => 'This schedule overlaps with another lesson schedule.',
                'errors' => [
                    'lesson_schedules.1.schedule_start' => [
                        'This schedule overlaps with another lesson schedule.'
                    ]
                ]
            ]);
    }

    /** @test */
    public function authenticated_master_with_subscription_can_create_lesson()
    {
        $apiUrl = "api/v1/lessons";
        $user   = User::factory()->create();
        $plan   = Plan::where('slug', 'basic-plan')->first();

        //Arrange Create Free Plan With Subscription Limit 3 Lessons
        (new SubscriptionService)->createSubscription($user, $plan, 'tok_visa_debit', $user->full_name);

        $this->actingAs($user)
            ->postJson($apiUrl, $this->payload())
            ->assertSuccessful()
            ->assertJsonStructure($this->assertJsonStructure());

        $this->assertDatabaseHas('master_lessons', [
            'title' => data_get($this->payload(), 'title'),
            'description' => data_get($this->payload(), 'description'),
        ]);

        // Assert that the lesson's cover photo was uploaded
        $masterLesson = MasterLesson::where('title', data_get($this->payload(), 'title'))->first();
        $this->assertNotNull($masterLesson->cover);

        foreach ($masterLesson->schedules as $scheduleData) {
            $this->assertDatabaseHas('lesson_schedules', [
                'schedule_start' => $scheduleData['schedule_start'],
                'duration_in_hours' => $scheduleData['duration_in_hours'],
                'master_lesson_id' => $masterLesson->id,
            ]);
        }
    }

    /**
     * @test
     * @group LessonController
     * @dataProvider lessonValidations
     */
    public function form_validates_lesson_creation($payload, $key)
    {
        $user   = User::factory()->create();
        $apiUrl = "api/v1/lessons";

        $response = $this->actingAs($user)->postJson($apiUrl, $payload);

        $response->assertJsonValidationErrors($key)
            ->assertUnprocessable();
    }

    public function lessonValidations()
    {
        $payload = $this->payload();

        return [
            'title.required'                => [Arr::except($payload, 'title'), 'title'],
            'description.required'          => [Arr::except($payload, 'description'), 'description'],
            'description.max'               => [Arr::except($payload, 'description', Str::random(1000)), 'description'],
            'lesson_price.required'         => [Arr::except($payload, 'lesson_price'), 'lesson_price'],
            'lesson_price.numeric'          => [Arr::except($payload, 'lesson_price', Str::random(1)), 'lesson_price'],
            'lesson_price.min'              => [Arr::except($payload, 'lesson_price', 0), 'lesson_price'],
            'lesson_schedules.required'     => [Arr::except($payload, 'lesson_schedules'), 'lesson_schedules'],
            'lesson_schedules.array'        => [Arr::except($payload, 'lesson_schedules', []), 'lesson_schedules'],
            'lesson_schedules.*.schedule_start.after_or_equal' => [Arr::except($payload, 'lesson_schedules', [
                'schedule_start' => now()->subDays(1),
            ]), 'lesson_schedules'],
            'lesson_schedules.*.schedule_start.date_format'   => [Arr::except($payload, 'lesson_schedules', [
                'schedule_start' => now()->addDays(1),
            ]), 'lesson_schedules'],
            'lesson_schedules.*.duration_in_hours.integer'    => [Arr::except($payload, 'lesson_schedules', [
                'duration_in_hours' => 'string',
            ]), 'lesson_schedules'],
            'category_id.required'          => [Arr::except($payload, 'category_id'), 'category_id'],
            'category_id.exists'            => [Arr::except($payload, 'category_id', null), 'category_id'],
            'is_remote_supported.required'  => [Arr::except($payload, 'is_remote_supported'), 'is_remote_supported'],
            'cover_photo.required'          => [Arr::except($payload, 'cover_photo'), 'cover_photo'],
            'cover_photo.array'             => [Arr::except($payload, 'cover_photo',  UploadedFile::fake()->image('photo1.png')), 'cover_photo'],
            'cover_photo.image'             => [Arr::except($payload, 'cover_photo',  UploadedFile::fake()->image('file.pdf')), 'cover_photo'],
            'tags.required'                 => [Arr::except($payload, 'tags'), 'tags'],
            'tags.array'                    => [Arr::except($payload, 'tags',  Str::random(1)), 'tags'],
            'timezone.required'             => [Arr::except($payload, 'timezone'), 'timezone'],
            'timezone.timezone' => [Arr::except($payload, 'timezone', 'Invalid Timezone'), 'timezone'],
            'address_or_link.required'      => [Arr::except($payload, 'address_or_link'), 'address_or_link'],
            'address_or_link.max' => [Arr::except($payload, 'address_or_link', Str::random(1000)), 'address_or_link'],
            'suburb.required'               => [Arr::except($payload, 'suburb'), 'suburb'],
            'state.required'                => [Arr::except($payload, 'state'), 'state'],
            'postcode.required'             => [Arr::except($payload, 'postcode'), 'postcode'],
            'postcode.max'              => [Arr::except($payload, 'postcode', Str::random(1000)), 'postcode'],
            'state.max'                 => [Arr::except($payload, 'state', Str::random(1000)), 'state'],
            'suburb.max'                => [Arr::except($payload, 'suburb', Str::random(1000)), 'suburb'],
        ];
    }

    public function payload()
    {
        return [
            'title'             => 'Test Lesson',
            'description'       => 'This is a test lesson.',
            'lesson_price'      => $this->randomBetween,
            'category_id'       => $this->categoryID,
            'place_id'          => $this->placeID,
            'lesson_schedules'    => $this->schedules,
            'is_remote_supported'   => true,
            'tags'                  => $this->tags,
            'cover_photo' => [
                UploadedFile::fake()->image('photo1.png'),
                UploadedFile::fake()->image('photo2.png'),
            ],
            'suburb' => 'Sydney',
            'state' => 'NSW',
            'postcode' => '2000',

            'timezone' => 'Australia/Sydney',
            'address_or_link' => 'https://www.google.com',
        ];
    }

    private function assertJsonStructure()
    {
        return [
            'data' => [
                'id',
                'user_id',
                'title',
                'slug',
                'description',
                'duration_in_hours',
                'lesson_price',
                'is_remote_supported',
                'active',
                'is_owner',
                'is_enrolled',
                'active',
                'place',
                'category',
                'tags',
                'cover_photo',
                'created_at',
            ],
        ];
    }


    private function seedPlans()
    {
        $this->artisan('db:seed', ['--class' => 'CountriesTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'PlacesTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'PlanTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'CategoriesTableSeeder']);
    }
}
