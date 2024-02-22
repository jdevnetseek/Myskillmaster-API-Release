<?php

namespace Tests\Feature\Controllers\V1\MarketPlace\ProductController;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Tests\Factories\ProductFactory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     */
    public function userAbleToGetListOfProducts()
    {
        $user = User::factory()->create();
        /** @var User */
        $seller = User::factory()->create([
            'payouts_enabled' => true
        ]);

        Product::factory()->times(10)->create([
            'seller_type' => $seller->getMorphClass(),
            'seller_id'   => $seller->getKey(),
        ]);

        $response = $this->actingAs($user)->getJson(route('products.index'));

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                [
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
                ]
            ],
        ]);
    }

    /**
     * @test
     */
    public function productsShouldNotBeIncludedWhenSellerPayoutsIsFalse()
    {
        $user = User::factory()->create();

        /** @var User */
        $seller = User::factory()->create(['payouts_enabled' => true]);

        /** @var User */
        $secondarySeller = User::factory()->create(['payouts_enabled' => false]);

        Product::factory()->times(3)->create([
            'seller_type' => $seller->getMorphClass(),
            'seller_id'   => $seller->getKey(),
        ]);

        Product::factory()->times(5)->create([
            'seller_type' => $secondarySeller->getMorphClass(),
            'seller_id'   => $secondarySeller->getKey(),
        ]);

        $response = $this->actingAs($user)->getJson(route('products.index'));

        $response->assertStatus(200);

        $response->assertJsonCount(3, 'data');
    }


    /**
     * @test
     */
    public function productWithOrdersShouldNotBeIncluded()
    {
        $user = User::factory()->create();

        /** @var User */
        $seller = User::factory()->create(['payouts_enabled' => true]);

        /** @var Product */
        $product = Product::factory()->create([
            'seller_type' => $seller->getMorphClass(),
            'seller_id'   => $seller->getKey(),
        ]);

        $product->orders()->forceCreate([
            'product_id'    => $product->id,
            'payment_id'    => $this->faker->uuid,
            'amount'        => '$12.00',
            'raw_amount'    => '1200'
        ]);

        Product::factory()->times(3)->create([
            'seller_type' => $seller->getMorphClass(),
            'seller_id'   => $seller->getKey(),
        ]);

        $response = $this->actingAs($user)->getJson(route('products.index'));

        $response->assertStatus(200);

        $response->assertJsonCount(3, 'data');
    }
}
