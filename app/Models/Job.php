<?php

namespace App\Models;

use App\Enums\MediaCollectionType;
use App\Models\Traits\HasComments;
use App\Models\Traits\HasLocation;
use App\Models\Interfaces\HasMedia;
use App\Models\Traits\CanBeFavorite;
use App\Models\Traits\CanBeReported;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Job extends Model implements HasMedia
{
    use HasComments;
    use HasFactory;
    use HasLocation;
    use SoftDeletes;
    use CanBeFavorite;
    use CanBeReported;
    use InteractsWithMedia;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'job_offers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'subcategory_id',
        'title',
        'description',
        'price_offer',
        'suburb'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'category_id'    => 'int',
        'subcategory_id' => 'int',
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
        $this->addMediaCollection($this->defaultCollectionName())
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')->width(254);
            });
    }

    /**
     * The default collection name to be use.
     *
     * @return string
     */
    public function defaultCollectionName() : string
    {
        return MediaCollectionType::JOB_PHOTOS;
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Job Photos
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function photos()
    {
        return $this->morphMany(Media::class, 'model')
            ->where('collection_name', $this->defaultCollectionName());
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
     * Subcategory
     *
     * @return void
     */
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class, 'subcategory_id');
    }
    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Filter result search result using fulltext index
     *
     * @param Builder $query
     * @param string $search
     * @return void
     */
    public function scopeSearch(Builder $query, string $search)
    {
        $query->whereRaw('match(title, description) against (? in boolean mode)', [ $search ]);
    }

    /**
     * Filter Jobs that only are popular.
     * NOTE: We will limit the counting from last 30 days, so that old post with higher
     * score will always not be on the popular list.
     *
     * 1 points per Favorites
     * We will distinctly count user_id to prevent having high score if a
     * bug manages to sneek in our code and save multiple favorite entries.
     *
     * -0.5 points per Report
     * 1 report by user per job post will be considered as 1. This way if the user
     * tries to flood a report, the job post score will not degrade to oblivion.
     *
     * We will consider job post as popular if they have greater than 0 score.
     *
     * @todo Test on large data set.
     * @param Builder $query
     * @return void
     */
    public function scopeOnlyPopular(Builder $query)
    {
        $query->joinSub(function ($query) {
            $query->selectRaw('sum(score) as _popularity_score, foreign_id as _foreign_id')
                ->from(function ($query) {
                    $fromDate = now()->subDays(30);
                    // Favorites
                    $query->selectRaw('count(DISTINCT user_id) as score, favoriteable_id as foreign_id')
                        ->from((new Favorite())->getTable())
                        ->whereFavoriteableType($this->getMorphClass())
                        ->where('created_at', '>=', $fromDate)
                        ->groupBy('foreign_id');
                    // Report
                    $query->union(
                        $query->newQuery()
                            ->selectRaw('(count(DISTINCT reported_by) * -0.5) as score, reportable_id as foreign_id')
                            ->from((new Report())->getTable())
                            ->whereReportableType($this->getMorphClass())
                            ->where('created_at', '>=', $fromDate)
                            ->groupBy('foreign_id')
                    );
                }, 'ranking')
                ->groupBy('_foreign_id');
        }, '_ranking', '_ranking._foreign_id', '=', $this->qualifyColumn('id'));

        $query->where('_popularity_score', '>', 0);
    }

    /**
     * Filter by suburb.
     *
     * @param Builder $query
     * @param string $term
     * @return void
     */
    public function scopeFilterBySuburb(Builder $query, string $term)
    {
        $query->where('suburb', 'like', "%${term}%");
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
