<?php

namespace Tests\Feature\Controllers\V1\MarketPlace\ProductController;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Enums\CategoryType;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use App\Exceptions\IncompleteStripeConnectPayout;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StoreTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @skip */
    public function userCanPostAProduct()
    {
        $user = User::factory()->create([
            'payouts_enabled' => true,
        ]);

        $payload = [
            'title'          => $this->faker->word,
            'description'    => $this->faker->sentence,
            'price'          => $this->faker->numberBetween(1000, 10000),
            'currency'       => config('cashier.currency'),
            'category_id'    => Category::factory()->productType()->create()->getKey(),
            'photos'         => [
                UploadedFile::fake()->image('sample1.png'),
                UploadedFile::fake()->image('sample2.png'),
            ],
        ];

        $response = $this->actingAs($user)->postJson(route('products.store'), $payload);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'price',
                'price_in_cents',
                'formatted_price',
                'currency',
                'category_id',
                'places_id',
                'places_address',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
        ]);
    }

    /** @skip */
    public function userWithPayoutsDisabledCannotPostProduct()
    {
        $user = User::factory()->create([
            'payouts_enabled' => false,
        ]);

        $payload = [
            'title'          => $this->faker->word,
            'description'    => $this->faker->sentence,
            'price'          => $this->faker->numberBetween(1000, 10000),
            'currency'       => config('cashier.currency'),
            'category_id'    => Category::factory()->productType()->create()->getKey(),
            'photos'         => [
                UploadedFile::fake()->image('sample1.png'),
                UploadedFile::fake()->image('sample2.png'),
            ],
        ];

        $response = $this->actingAs($user)->postJson(route('products.store'), $payload);

        $response->assertStatus(Response::HTTP_FORBIDDEN);

        $response->assertSee((new IncompleteStripeConnectPayout)->getMessage());
    }
}
