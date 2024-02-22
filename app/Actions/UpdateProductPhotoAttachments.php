<?php

namespace App\Actions;

use App\Models\Media;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use App\Enums\MediaCollectionType;
use Illuminate\Support\Collection;
use App\Models\Interfaces\HasMedia;

class UpdateProductPhotoAttachments
{
    /**
     * This function handles the adding and updating the order_column of
     * product photos.
     *
     * @todo This function requires improvements as this is quite heavy in
     * term of memory and query when large data set is passed.
     *
     * @param Product $product
     * @param array $photos
     * @return void
     */
    public function execute(Product $product, array $photos)
    {
        $order = 1;
        $orderedPhoto = [];

        foreach ($photos as $photo) {
            // if a delete property is present, delete the photo
            if (data_get($photo, 'delete')) {
                $product->photos()->where('id', data_get($photo, 'id'))->delete();
            } else {
                // if not, check if photo already exist.
                $media = $product->photos()->find(data_get($photo, 'id'));

                if (blank($media)) {
                    //If not, then assume it's a newly added photo.
                    $media = Media::query()
                        ->onlyUnassigned()
                        ->find(data_get($photo, 'id'));
                    // If found let's move it and assign the new value to our
                    // $media variable.
                    if (filled($media)) {
                        $media = $media->move($product, MediaCollectionType::PRODUCT_ATTACHMENTS);
                    }
                }
                //  and update order column
                if (filled($media)) {
                    $media->order_column = $order++;
                    $orderedPhoto[] = $media->id;
                    $media->save();
                }
            }
        }

        // Update the order of the photos that where not included in the list
        $product->photos()->whereNotIn('id', $orderedPhoto)
            ->orderBy('order_column')->each(function ($photo) use (&$order) {
                $photo->order_column = $order++;
                $photo->save();
            });
    }
}
