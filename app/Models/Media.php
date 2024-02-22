<?php

namespace App\Models;

use App\Enums\MediaCollectionType;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia
{
    /**
     * Get unassigned media
     *
     * @param Builder $query
     * @return void
     */
    public function scopeOnlyUnassigned(Builder $query)
    {
        $query->whereCollectionName(MediaCollectionType::UNASSIGNED);
    }

    /**
     * A helper to check if media is unassigned or not.
     *
     * @return boolean
     */
    public function isUnassigned() : bool
    {
        return $this->collection_name === MediaCollectionType::UNASSIGNED;
    }
}
