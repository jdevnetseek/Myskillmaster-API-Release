<?php

namespace App\Models\Traits;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\HasMedia;
use App\Enums\MediaCollectionType;
use Spatie\MediaLibrary\InteractsWithMedia;

trait HasAvatar
{
    use InteractsWithMedia;

    /**
     * Avatar Relationship
     */
    public function avatar()
    {
        return $this->morphOne(Media::class, 'model')
            ->where('collection_name', MediaCollectionType::AVATAR);
    }

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
        $this->addMediaCollection(MediaCollectionType::AVATAR)
            ->singleFile()
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')->width(254);
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Remove the avatar media.
     *
     * @return void
     */
    public function removeAvatar() : void
    {
        $this->touch();

        $this->avatar()->delete();
    }

    /**
     * Set the avatar of the user
     *
     * @param UploadedFile $file
     * @return Media
     */
    public function setAvatar(UploadedFile $file) : Media
    {
        // Hashing file name
        $name = md5(uniqid(self::class . $this->getKey(), true));
        $fileName = $name . '.' . $file->extension();

        $this->touch();

        return $this->addMedia($file)
            ->usingName($name)
            ->usingFileName($fileName)
            ->toMediaCollection(MediaCollectionType::AVATAR);
    }

    /**
     * Set the avatar using a provided media id.
     * Media Id should belong to unasigned Type.
     *
     * @param integer $mediaId
     * @return Media
     */
    public function setAvatarByMediaId(int $mediaId) : Media
    {
        /** @var HasMedia */
        $instance = $this;

        /** @var Media */
        $media = Media::onlyUnassigned()->findOrFail($mediaId);

        $this->touch();

        return $media->move($instance, MediaCollectionType::AVATAR);
    }
}
