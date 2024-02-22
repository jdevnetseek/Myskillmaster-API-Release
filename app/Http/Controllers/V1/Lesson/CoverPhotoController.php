<?php

namespace App\Http\Controllers\V1\Lesson;

use App\Models\Media;
use App\Models\MasterLesson;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;

class CoverPhotoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request, MasterLesson $lesson)
    {
        $this->authorize('update', $lesson);

        $request->validate([
            'images' => ['required', 'array'],
            'images.*' => ['image']
        ]);

        $storedMedia = collect();
        collect($request->images)->each(fn ($file) => $storedMedia->push($this->storeMedia($lesson, $file)));

        return MediaResource::collection($storedMedia);
    }

    public function destroy(MasterLesson $lesson, Media $media)
    {
        $this->authorize('update', $lesson);

        $media->delete();

        return $this->respondWithEmptyData();
    }

    protected function storeMedia($lesson, UploadedFile $file): Media
    {
        return $lesson->addMedia($file)
            ->usingName($file->hashName())
            ->toMediaCollection($lesson->defaultCollectionName());
    }
}
