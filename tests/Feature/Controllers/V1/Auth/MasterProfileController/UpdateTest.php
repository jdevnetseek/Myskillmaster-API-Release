<?php

namespace Tests\Feature\Controllers\V1\Auth\MasterProfileController;

use Tests\TestCase;
use App\Models\User;
use Database\Seeders\PlanTableSeeder;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Env;

class UpdateTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    private $route = 'api/v1/auth/master-profile';

    public $freePlan;
    public $stripeToken;

    public function setUp(): void
    {
        parent::setUp();

        Env::enablePutenv();

        $this->seedPlans();

        $paymentMethod = [
            'tok_visa', 'tok_visa_debit', 'tok_mastercard',
            'tok_mastercard_debit', 'tok_mastercard_prepaid', 'tok_amex',
            'tok_discover', 'tok_diners', 'tok_jcb', 'tok_unionpay'
        ];

        // $this->stripeToken = $this->faker->randomElement($paymentMethod);
    }

    private function seedPlans()
    {
        $this->artisan('db:seed', ['class' =>  PlanTableSeeder::class]);
    }

    /** @test */
    public function user_should_able_to_store_their_master_profile_information_without_portfolio()
    {
        $user = User::factory()->create();

        $data = $this->createData();

        $this->actingAs($user)
            ->postJson($this->route, $data)
            ->assertOk()
            ->assertJsonStructure([
                'data' => $this->expectedData(),
            ])
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->has('data', fn ($json) => $json->whereAllType($this->expectedDataTypes()))
                    ->etc()
            )
            ->assertJson([
                'data' => $data
            ]);
    }

    /** @test */
    public function user_should_able_to_store_their_master_profile_information_with_portfolio()
    {
        $user = User::factory()->create();

        $data = $this->createData();

        Storage::fake();

        $portfolio = [
            UploadedFile::fake()->image('portfolio0.png'),
            UploadedFile::fake()->image('portfolio1.jpg')
        ];

        $this->actingAs($user)
            ->postJson($this->route, array_merge($data, ['portfolio' => $portfolio]))
            ->assertOk()
            ->assertJsonStructure([
                'data' => $this->expectedData(),
            ])
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->has('data', fn ($json) => $json->whereAllType($this->expectedDataTypes()))
                    ->etc()
            )
            ->assertJson([
                'data' => $data
            ])
            ->assertJsonCount(count($portfolio), 'data.portfolio');
    }

    public function test_about_field_is_required()
    {
        $user = User::factory()->create();

        $data = $this->createData();

        $data['about'] = '';

        $this->actingAs($user)
            ->postJson($this->route, $data)
            ->assertInvalid([
                'about' => __('validation.required', ['attribute' => 'about'])
            ]);
    }

    public function test_about_field_must_only_contain_five_hundred_characters_max()
    {
        $user = User::factory()->create();

        $data = $this->createData();

        $data['about'] = Str::random(501);

        $this->actingAs($user)
            ->postJson($this->route, $data)
            ->assertInvalid([
                'about' => __(
                    'validation.max.string',
                    ['attribute' => 'about', 'max' => 500]
                )
            ]);
    }

    public function test_work_experiences_field_is_required()
    {
        $user = User::factory()->create();

        $data = $this->createData();

        $data['work_experiences'] = '';

        $this->actingAs($user)
            ->postJson($this->route, $data)
            ->assertInvalid([
                'work_experiences' => __('validation.required', ['attribute' => 'work experiences'])
            ]);
    }

    public function test_work_experiences_field_must_only_contain_five_hundred_characters_max()
    {
        $user = User::factory()->create();

        $data = $this->createData();

        $data['work_experiences'] = Str::random(501);

        $this->actingAs($user)
            ->postJson($this->route, $data)
            ->assertInvalid([
                'work_experiences' => __(
                    'validation.max.string',
                    ['attribute' => 'work experiences', 'max' => 500]
                )
            ]);
    }

    /** @test */
    public function user_should_be_able_to_uploaded_image_in_portfolio_field()
    {
        $user = User::factory()->create();

        $data = $this->createData();

        Storage::fake();

        $portfolio = [
            UploadedFile::fake()->image('portfolio0.png'),
            UploadedFile::fake()->create('test.txt'),
            UploadedFile::fake()->create('test.exe'),
            UploadedFile::fake()->create('test.sh')
        ];

        $this->actingAs($user)
            ->postJson($this->route, array_merge($data, ['portfolio' => $portfolio]))
            ->assertInvalid([
                'portfolio.1', 'portfolio.2', 'portfolio.3',
            ]);
    }

    private function createData(array $attrs = []): array
    {
        return array_merge([
            'about' => $this->faker->sentence(),
            'work_experiences' => $this->faker->sentence(10),
            'languages' => [
                'english',
                'spanish',
            ],
        ], $attrs);
    }

    private function expectedData()
    {
        return [
            'about',
            'work_experiences',
            'languages',
            'portfolio',
        ];
    }

    private function expectedDataTypes()
    {
        return [
            'about' => 'string',
            'work_experiences' => 'string',
            'languages' => 'array',
            'portfolio' => 'array',
        ];
    }
}
