<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use App\Enums\MediaCollectionType;
use App\Models\Traits\HasComments;
use App\Models\Traits\CanBeFavorite;
use App\Models\Traits\CanBeReported;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model implements HasMedia
{
    use CanBeFavorite;
    use CanBeReported;
    use HasComments;
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'body',
    ];

    /*
    |--------------------------------------------------------------------------
    | Media Collections
    |--------------------------------------------------------------------------
    */

    /**
     * Register media collections
     *
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollectionType::POST_PHOTOS)
            ->singleFile()
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')->width(254);
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Post Photos
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function photo()
    {
        return $this->morphOne(Media::class, 'model')->where('collection_name', MediaCollectionType::POST_PHOTOS);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Checks if the job offer is owned by the user.
     *
     * @param Builder $query
     * @param integer $userId
     * @return boolean
     */
    public function isOwner(User $user) : bool
    {
        return $this->author_id === $user->getKey();
    }
}
