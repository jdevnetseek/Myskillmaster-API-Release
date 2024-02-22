<?php

namespace App\Http\Controllers\V1\User;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedInclude;

class UserPostController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, User $user)
    {
        $collection = QueryBuilder::for(Post::class)
            ->appendIsFavoriteAsUser($request->user())
            ->whereAuthorId($user->getKey())
            ->allowedIncludes([
                'photo',
                'author',
                'favoritesCount',
                AllowedInclude::count('commentsCount', 'allComments')
            ])
            ->allowedSorts('created_at')
            ->defaultSort('-created_at')
            ->paginate($request->perPage());

        return PostResource::collection($collection);
    }
}
