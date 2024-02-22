<?php

namespace App\Models;

use App\Actions\SyncModelAttachments;
use App\Enums\MediaCollectionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Models\Interfaces\HasMedia;
use App\Models\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Interfaces\Notifiable as NotifiableInterface;
use App\Models\Traits\CanBeReported;
use App\Models\Traits\HasAddresses;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Tags\HasTags;

class MasterLesson extends Model implements HasMedia, NotifiableInterface
{
    use HasFactory;
    use InteractsWithMedia;
    use HasSlug;
    use HasTags;
    use Notifiable;
    use HasAddresses;
    use CanBeReported;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'duration_in_hours',
        'lesson_price',
        'currency',
        'place_id',
        'is_remote_supported',
        'active',
        'address_or_link',
        'suburb',
        'postcode',
        'state',
    ];

    protected $with = ['tags'];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cover(): MorphMany
    {
        return $this->morphMany(Media::class, 'model')
            ->where('collection_name', $this->defaultCollectionName());
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'place_id');
    }

    public function enrollments()
    {
        return $this->hasMany(LessonEnrollment::class, 'lesson_id');
    }

    public function addCoverPhoto(array $cover): void
    {
        resolve(SyncModelAttachments::class)->execute($this, collect($cover));
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(LessonSchedule::class)
            ->orderBy('schedule_start');
    }

    /**
     * Scope
     */
    public function scopeOwnedBy(Builder $query, $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeSearch(Builder $query, $search): Builder
    {
        return $query->where(function ($query) use ($search) {
            $query->where('title', 'LIKE', "%$search%")
                ->orWhereHas('tags', function ($query) use ($search) {
                    $lowercaseString = strtolower($search);
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('name', 'like', '%' . $lowercaseString . '%');
                });
        });
    }

    public function scopeSearchMaster(Builder $query, $search): Builder
    {
        return $query->whereHas('user', function ($query) use ($search) {
            $query->whereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%$search%"])
                ->orWhere('email', $search);
        });
    }

    public function scopePopularLesson(Builder $query)
    {
        return $query->where('active', '<>', 0)
            ->whereHas('user', function ($query) {
                $query->whereHas('masterProfile')
                    ->whereHas('subscriptions', fn ($query) => $query->active());
            });
    }

    public function scopeOnlyMasterLessons(Builder $query)
    {
        return $query->whereHas('user', function ($query) {
            $query->whereHas('masterProfile')
                ->whereHas('subscriptions', fn ($query) => $query->active());
        });
    }

    /**
     * Misc
     */

    public function defaultCollectionName(): string
    {
        return MediaCollectionType::LESSON_COVER_PHOTO;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection($this->defaultCollectionName())
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')->width(254);
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title') // set the attribute to generate the slug from
            ->saveSlugsTo('slug'); // set the attribute to save the slug to
    }

    /**
     * Checks if the lesson is owned by the user.
     *
     * @param Builder $query
     * @param integer $userId
     * @return boolean
     */
    public function isOwner(User $user): bool
    {
        return $this->user_id === $user->getKey();
    }

    /**
     * Checks if the lesson owner is subscribe to any plan.
     *
     * @param Builder $query
     * @return boolean
     */
    public function scopeIsUserSubscribe(Builder $query)
    {
        $query->whereHas('user', function ($query) {
            $query->onlyMaster();
        });
    }

    public function scopeSorted(Builder $query): Builder
    {
        return $query->orderBy('active', 'desc')
            ->orderBy('title');
    }

    /**
     * validate before delete of the lesson if it has student enrolled
     * @return boolean
     */
    public function hasEnrolledStudent(): bool
    {
        return $this->enrollments()->count() > 0;
    }

    /**
     * Check if authenticated user is enrolled to the lesson
     */
    public function isEnrolled(): bool
    {
        // @todo: Fix n + 1
        return $this->enrollments()
            ->where('student_id', optional(auth()->user())->id)
            ->notCancelled()
            ->whereHas('schedule', fn ($query) => $query->upcoming())
            ->exists();
    }

    public function isEnrolledToPastLesson(): bool
    {
        return $this->enrollments()
            ->where('student_id', optional(auth()->user())->id)
            ->notCancelled()
            ->whereHas('schedule', fn ($query) => $query->completed())
            ->exists();
    }

    public function isReportableByUser(?User $user): bool
    {
        if (is_null($user)) {
            return false;
        }

        return $this->enrollments->where('student_id', $user->getKey())->isNotEmpty();
    }
}
