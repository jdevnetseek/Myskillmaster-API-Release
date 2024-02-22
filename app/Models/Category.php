<?php

namespace App\Models;

use Spatie\Sluggable\HasSlug;
use Illuminate\Http\UploadedFile;
use Spatie\Sluggable\SlugOptions;
use App\Enums\MediaCollectionType;
use App\Models\Interfaces\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model implements HasMedia
{
    use HasFactory;
    use HasSlug;
    use InteractsWithMedia;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'label',
        'slug',
        'type',
    ];

    const KEYWORD_DELIMITER = ',';

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * Define a one-to-many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subcategories()
    {
        return $this->hasMany(Subcategory::class, 'parent_id');
    }

    public function icon()
    {
        return $this->morphOne(Media::class, 'model')
            ->where('collection_name', $this->defaultCollectionName());
    }

    public function lessons()
    {
        return $this->hasMany(MasterLesson::class, 'category_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scope
    |--------------------------------------------------------------------------
    */

    /**
     * Filter all category that are top and first child only.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOnlyTopParent(Builder $query)
    {
        $query->whereNull('parent_id');
    }

    public function scopeType(Builder $query, string $type)
    {
        $query->whereType($type);
    }

    public function scopeSearch(Builder $query, $search)
    {
        $query->where('label', 'like', "%$search%");
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Register media collections
     *
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollectionType::CATEGORY_ICON)
            ->singleFile()
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')->width(254);
            });
    }

    public function setIcon(UploadedFile $file): Media
    {
        return $this->addMedia($file)
            ->toMediaCollection($this->defaultCollectionName());
    }

    public function defaultCollectionName(): string
    {
        return MediaCollectionType::CATEGORY_ICON;
    }

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('label')
            ->saveSlugsTo('slug');
    }
}
