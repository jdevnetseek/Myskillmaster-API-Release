<?php

namespace App\Http\Controllers\V1\Post;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Enums\MediaCollectionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportRequest;
use App\Http\Resources\PostResource;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Requests\PostStoreRequest;
use Spatie\QueryBuilder\AllowedInclude;
use App\Http\Requests\PostUpdateRequest;

class PostController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->authorizeResource(Post::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $collection = QueryBuilder::for(Post::class)
            ->appendIsFavoriteAsUser($request->user())
            ->allowedIncludes([
                'photo', 'author', 'favoritesCount', AllowedInclude::count('commentsCount', 'allComments')
            ])
            ->allowedFilters(AllowedFilter::exact('author_id')->ignore(null))
            ->allowedSorts('created_at')
            ->defaultSort('-created_at')
            ->paginate($request->perPage());

        return PostResource::collection($collection);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PostStoreRequest $request)
    {
        $post = $request->user()->posts()->create($request->validated());

        $post->addMedia($request->file('photo'))->toMediaCollection(MediaCollectionType::POST_PHOTOS);

        return PostResource::make($post->load('photo', 'author'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Post $post)
    {
        $post->load('author', 'photo');
        $post->loadCount('allComments', 'favorites');
        $post->is_favorite = $post->isFavorite();

        return PostResource::make($post);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PostUpdateRequest $request, Post $post)
    {
        $post->update($request->validated());

        return PostResource::make($post->load('photo'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return response()->json([], Response::HTTP_OK);
    }


    /**
     * Handles a request in adding jobs to favorite.
     *
     * @param Post $post
     * @return void
     */
    public function favorite(Post $post)
    {
        $post->favorite();

        return response()->json([], Response::HTTP_OK);
    }

    /**
     * Handles a request in removing jobs from favorite.
     *
     * @param Post $post
     * @return void
     */
    public function unfavorite(Post $post)
    {
        $post->unfavorite();

        return response()->json([], Response::HTTP_OK);
    }

    /**
     * Handles Request when a job has been reported.
     *
     * @deprecated 1.6 use /report
     *
     * @param ReportRequest $request
     * @param Post $post
     * @return void
     */
    public function report(ReportRequest $request, Post $post)
    {
        $post->report(
            $request->input('reason_ids'),
            $request->input('description'),
            $request->file('attachments', [])
        );

        return response()->json([], Response::HTTP_OK);
    }
}
