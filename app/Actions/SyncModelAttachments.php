<?php

namespace App\Actions;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use App\Models\Interfaces\HasMedia;

class SyncModelAttachments
{
    /** @var HasMedia */
    protected $model;

    /** @var Collection */
    protected $attachments;

    /**
     * Execute this action
     *
     * @param HasMedia $model
     * @param Collection $attachments
     * @return void
     */
    public function execute(HasMedia $model, Collection $attachments)
    {
        $this->model       = $model;
        $this->attachments = $attachments;

        $this->addUploadedFiles();
        $this->addFilesById();
        $this->removeMarkedFiles();
    }


    /**
     * Handles the saving of uploaded files.
     *
     * @return void
     */
    protected function addUploadedFiles()
    {
        $uploadedFile = $this->attachments->filter(function ($item) {
            return $item instanceof UploadedFile;
        });

        $uploadedFile->each(function ($file) {
            $this->model->addMedia($file)
                ->toMediaCollection($this->model->defaultCollectionName());
        });
    }

    /**
     * Handles the saving of files that was marked as unsigned.
     *
     * @return void
     */
    protected function addFilesById()
    {
        $addedFiles = $this->attachments->filter(function ($item) {
            return !data_get($item, 'delete');
        })->pluck('id');

        if (count($addedFiles)) {
            Media::query()
                ->onlyUnassigned()
                ->whereIn('id', $addedFiles)
                ->get()
                ->each(function (Media $media) {
                    $media->move($this->model, $this->model->defaultCollectionName());
                });
        }
    }

    /**
     * Handles the deletiong of files.
     *
     * @return void
     */
    protected function removeMarkedFiles()
    {
        $removedFiles = $this->attachments->filter(function ($item) {
            return data_get($item, 'delete');
        })->pluck('id');

        if (count($removedFiles)) {
            $this->model->media()
                ->whereIn('id', $removedFiles)
                ->where('collection_name', $this->model->defaultCollectionName())
                ->delete();
        }
    }
}
