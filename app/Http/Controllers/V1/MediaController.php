<?php

namespace App\Http\Controllers\V1;

use App\Models\User;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use League\Glide\ServerFactory;
use App\Enums\MediaCollectionType;
use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
use Illuminate\Contracts\Filesystem\Filesystem;
use League\Glide\Responses\LaravelResponseFactory;
use Spatie\MediaLibrary\Support\UrlGenerator\BaseUrlGenerator;
use Spatie\MediaLibrary\Support\UrlGenerator\UrlGeneratorFactory;

class MediaController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->except('imageFactory');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(['file' => 'required|image']);

        /** @var User */
        $user  = $request->user();

        $media = $user->addMediaFromRequest('file')
            ->toMediaCollection(MediaCollectionType::UNASSIGNED);

        return MediaResource::make($media);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $media = Media::findOrFail($id);

        return MediaResource::make($media);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $media = Media::findOrFail($id);
        $media->delete();

        return response()->json([], Response::HTTP_OK);
    }

    /**
     * Handle the request for processing image on the fly.
     *
     * @param Request $request
     * @param Filesystem $filesystem
     * @param Media $media
     * @return void
     */
    public function imageFactory(Request $request, Filesystem $filesystem, Media $media)
    {
        $server = ServerFactory::create([
            'response'           => new LaravelResponseFactory($request),
            'source'             => $filesystem->getDriver(),
            'cache'              => $filesystem->getDriver(),
            'source_path_prefix' => 'public',
            'cache_path_prefix'  => 'cache'
        ]);

        /** @var BaseUrlGenerator */
        $urlGenerator = UrlGeneratorFactory::createForMedia($media);

        return $server->getImageResponse($urlGenerator->getPathRelativeToRoot(), $request->all());
    }
}
