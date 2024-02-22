<?php

namespace App\Http\Controllers\V1\MarketPlace;

use App\Models\User;
use App\Models\Media;
use App\Models\Product;
use App\Models\Category;
use App\Enums\CategoryType;
use Illuminate\Http\Request;
use App\Enums\MediaCollectionType;
use Illuminate\Support\Facades\DB;
use SKAgarwal\GoogleApi\PlacesApi;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\AllowedSort;
use App\Actions\SyncModelAttachments;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Resources\ProductResource;
use App\Http\Resources\CategoryResource;
use App\Actions\UpdateProductPhotoAttachments;
use App\Http\Requests\Products\StoreRequest as ProductStoreRequest;
use App\Http\Requests\Products\UpdateRequest as ProductUpdateRequest;

class ProductController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->authorizeResource(Product::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @todo Add unit test
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $collection = QueryBuilder::for(Product::class)
            ->doesntHave('orders')
            ->whereHasMorph('seller', [User::class], function ($query) {
                $query->where('payouts_enabled', true);
            })
            ->allowedIncludes('photos', 'seller', 'category')
            ->allowedFilters(
                AllowedFilter::scope('search')->ignore(null),
                AllowedFilter::scope('exclude_current_user')->ignore(null),
                AllowedFilter::exact('places_id'),
                AllowedFilter::exact('category_id'),
                AllowedFilter::exact('seller_id'),
                AllowedFilter::scope('price_range', 'WithinPriceRange'),
                AllowedFilter::callback('within_distance_to', function ($query, $value) {
                    abort_if(count($value) != 3, 422, 'Invalid arguments for within_distance_to.');
                    $query->appendCoordinates();
                    $query->appendDistanceTo($value[0], $value[1]);
                    $query->withinDistanceTo($value[0], $value[1], $value[2]);
                })
            )
            ->allowedSorts([
                'created_at',
                'title',
                AllowedSort::field('price', 'price_in_cents')
            ])
            ->defaultSort('-created_at')
            ->paginate($request->perPage());

        return ProductResource::collection($collection);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @todo Add unit test
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductStoreRequest $request)
    {
        $product = DB::transaction(function () use ($request) {
            /** @var User */
            $user = auth()->user();

            /** @var Product */
            $product = $user->products()->create($request->validated());

            if ($request->has('places_id')) {
                $product->setPlacesDetails($request->input('places_id'));
            }

            return $product;
        });

        if ($request->hasFile('photos')) {
            // Let's add the files in a proper order.
            $order = 1;
            collect($request->file('photos'))
                ->each(function ($file) use ($product, &$order) {
                    $media = $product->addMedia($file)
                        ->toMediaCollection(MediaCollectionType::PRODUCT_ATTACHMENTS);
                    $media->order_column = $order++;
                    $media->save();
                });
        }

        return ProductResource::make($product->fresh('photos', 'seller', 'category'));
    }

    /**
     * Display the specified resource.
     *
     * @todo Add unit test
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return ProductResource::make($product->load('photos', 'seller', 'category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @todo Add unit test
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductUpdateRequest $request, UpdateProductPhotoAttachments $syncPhoto, Product $product)
    {
        $product->update($request->validated());

        if ($request->has('places_id')) {
            $product->setPlacesDetails($request->input('places_id'));
        }

        if ($request->has('photos')) {
            $syncPhoto->execute($product, $request->input('photos'));
        }

        return ProductResource::make($product->load('photos', 'seller', 'category'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @todo Add unit test
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return $this->respondWithEmptyData();
    }

    /**
     * Handles the request for list of similar products function
     *
     * @param Product $product
     * @return void
     */
    public function similar(Request $request, Product $product)
    {
        $collection = QueryBuilder::for(Product::class)
            ->doesntHave('orders')
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->getKey())
            ->whereHasMorph('seller', [User::class], function ($query) {
                $query->where('payouts_enabled', true);
            })
            ->allowedIncludes('photos', 'seller', 'category')
            ->allowedFilters(
                AllowedFilter::scope('search')->ignore(null),
                AllowedFilter::scope('exclude_current_user')->ignore(null),
                AllowedFilter::exact('places_id'),
                AllowedFilter::exact('category_id'),
                AllowedFilter::exact('seller_id'),
                AllowedFilter::scope('price_range', 'WithinPriceRange'),
                AllowedFilter::callback('within_distance_to', function ($query, $value) {
                    abort_if(count($value) != 3, 422, 'Invalid arguments for within_distance_to.');
                    $query->appendCoordinates();
                    $query->appendDistanceTo($value[0], $value[1]);
                    $query->withinDistanceTo($value[0], $value[1], $value[2]);
                })
            )
            ->allowedSorts([
                'created_at',
                'title',
                AllowedSort::field('price', 'price_in_cents')
            ])
            ->defaultSort('-created_at')
            ->paginate($request->perPage());

        return ProductResource::collection($collection);
    }

    /**
     * List of product categories.
     *
     * @return void
     */
    public function categories()
    {
        $collection = QueryBuilder::for(Category::class)
            ->onlyTopParent()
            ->where('type', CategoryType::PRODUCT)
            ->allowedSorts('label')
            ->defaultSort('created_at')
            ->get();

        return CategoryResource::collection($collection);
    }
}
