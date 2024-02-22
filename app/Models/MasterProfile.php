<?php

namespace App\Models;

use App\Actions\SyncModelAttachments;
use Illuminate\Support\Str;
use App\Enums\MediaCollectionType;
use App\Models\Interfaces\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MasterProfile extends Model implements HasMedia
{
    use InteractsWithMedia;
    use HasFactory;

    protected $fillable = ['about', 'work_experiences'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function portfolio(): MorphMany
    {
        return $this->morphMany(Media::class, 'model')
            ->where('collection_name', $this->defaultCollectionName());
    }

    public function languages(): HasMany
    {
        return $this->hasMany(MasterLanguage::class);
    }

    /**
     * Helpers
     */

    /**
     * Set languages of master
     * This will remove all the languages not provided in the paramter
     *
     * @param array<string> $languages
     */
    public function setLanguages(array $languages): void
    {
        $savedLanguagesIds = [];

        foreach ($languages as $language) {

            if (empty($language)) {
                continue;
            }

            $l = $this->languages->firstWhere('name', Str::lower($language));

            if (!$l) {
                $l = $this->languages()->create([
                    'name' => Str::lower($language)
                ]);
            }


            $savedLanguagesIds[] = $l->getKey();
        }

        $this->languages()->whereNotIn('id', $savedLanguagesIds)->delete();
    }

    public function addPortfolio(array $portfolio): void
    {
        resolve(SyncModelAttachments::class)->execute($this, collect($portfolio));
    }


    /**
     * Misc
     */

    public function defaultCollectionName(): string
    {
        return MediaCollectionType::PORTFOLIO;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection($this->defaultCollectionName())
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')->width(254);
            });
    }
}
