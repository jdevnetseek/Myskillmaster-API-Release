<?php

namespace App\Models;

use NumberFormatter;
use App\Enums\MediaCollectionType;
use App\Models\Traits\HasLocation;
use App\Models\Interfaces\HasMedia;
use Illuminate\Support\Facades\App;
use App\Models\Traits\CanBeReported;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model implements
    HasMedia
{
    use HasFactory;
    use SoftDeletes;
    use InteractsWithMedia;
    use HasLocation;
    use CanBeReported;

    const CENTS = 100;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'currency',
        'price_in_cents',
        'price',
        'places_id',
        'category_id'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'category_id'    => 'int',
        'price_in_cents' => 'int',
        'price'          => 'float',
    ];

    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    */

    /**
     * A mutator to convert the dollar value into cents.
     *
     * @param mixed $value
     * @return void
     */
    public function setPriceAttribute($value)
    {
        $this->attributes['price_in_cents'] = $value * static::CENTS;
    }


    /*
    |--------------------------------------------------------------------------
    | Accessor
    |--------------------------------------------------------------------------
    */

    /**
     * Accessor to convert the current value of price in cents to dollar.
     *
     * @return void
     */
    public function getPriceAttribute()
    {
        return round($this->attributes['price_in_cents'] / static::CENTS, 2);
    }

    /**
     * Accessor to add a dollar sign on the price.
     *
     * @return string
     */
    public function getFormattedPriceAttribute()
    {
        return (new NumberFormatter(App::getLocale(), NumberFormatter::CURRENCY))
            ->formatCurrency($this->price, strtoupper($this->currency));
    }

    /*
    |--------------------------------------------------------------------------
    | Media Collections
    |--------------------------------------------------------------------------
    */

    /**
     * The default collection name to be use.
     *
     * @return string
     */
    public function defaultCollectionName(): string
    {
        return MediaCollectionType::PRODUCT_ATTACHMENTS;
    }

    /**
     * Register media collections
     *
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection($this->defaultCollectionName())
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')->width(254);
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Define a polymorphic, inverse one-to-one or many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function seller()
    {
        return $this->morphTo();
    }

    /**
     * Product Photos
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function photos()
    {
        return $this->morphMany(Media::class, 'model')
            ->where('collection_name', $this->defaultCollectionName())
            ->orderBy('order_column');
    }

    /**
     * Category
     *
     * @return void
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Product Orders.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orders()
    {
        return $this->hasMany(ProductOrder::class, 'product_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scope
    |--------------------------------------------------------------------------
    */

    /**
     * Filter results by search query.
     *
     * @param Builder $query
     * @param string $search
     * @return void
     */
    public function scopeSearch(Builder $query, string $search)
    {
        $query->where('title', 'LIKE', "%$search%");
    }

    /**
     * Exclude the current authenticated users posted products.
     *
     * @param Builder $query
     * @return void
     */
    public function scopeExcludeCurrentUser(Builder $query)
    {
        /** @var User */
        if ($user = auth()->user()) {
            $query->where('seller_id', '!=', $user->getKey());
        }
    }

    /**
     * Filter results that are within range of the price specified.
     *
     * When the value of $lowest or $highest is -1, we will assume
     * that the user wants to have any of the value for that price.
     *
     * If both where -1, we will just ignore the scope to return any of the items
     * from the database.
     *
     * @param Builder $query
     * @param float $lowest
     * @param float $highest
     * @return void
     */
    public function scopeWithinPriceRange(Builder $query, float $lowest, float $highest)
    {
        $any = -1.0;

        if ($lowest === $any && $highest !== $any) {
            $query->where('price_in_cents', '<=', $highest * static::CENTS);
        } elseif ($lowest !== $any && $highest === $any) {
            $query->where('price_in_cents', '>=', $lowest * static::CENTS);
        } elseif ($lowest !== $any && $highest !== $any) {
            $query->whereBetween('price_in_cents', [$lowest * static::CENTS, $highest * static::CENTS]);
        }
    }

    /**
     * Add a the address and set the place location of the product
     *
     * @param string $placeId
     * @return bool
     */
    public function setPlacesDetails(string $placeId)
    {
        $place  = app(\SKAgarwal\GoogleApi\PlacesApi::class)->placeDetails($placeId);

        $this->places_id      = $placeId;
        $this->places_address = data_get($place, 'result.formatted_address');

        $this->setLocation(
            data_get($place, 'result.geometry.location.lat'),
            data_get($place, 'result.geometry.location.lng')
        );

        return $this->save();
    }

    /**
     * Checks if the product is owned by the user.
     *
     * @param Builder $query
     * @param integer $userId
     * @return boolean
     */
    public function isOwner(User $user): bool
    {
        return $this->seller_id === $user->getKey();
    }
}
