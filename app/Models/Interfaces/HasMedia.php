<?php

namespace App\Models\Interfaces;

use Spatie\MediaLibrary\HasMedia as SpatieHasMedia;

interface HasMedia extends SpatieHasMedia
{
    /**
     * The default collection name to be use.
     *
     * @return string
     */
    public function defaultCollectionName() : string;
}
