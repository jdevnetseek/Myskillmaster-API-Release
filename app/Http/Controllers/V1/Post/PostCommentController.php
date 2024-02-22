<?php

namespace App\Http\Controllers\V1\Post;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CommentRequest;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Resources\CommentResource;
use App\Support\QueryBuilder\AllowedIncludeExtended;

class PostCommentController extends Controller
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Post $post)
    {
        $collection = QueryBuilder::for(Comment::class)
            ->whereCommentableType($post->getMorphClass())
            ->whereCommentableId($post->getKey())
            ->allowedIncludes([
                'author',
                'responsesCount',
                AllowedIncludeExtended::relationshipBuilder('responses', function ($query) {
                    $query->with('author');
                    $query->latest();
                })
            ])
            ->allowedSorts('created_at')
            ->defaultSort('-created_at')
            ->paginate($request->perPage());

        return CommentResource::collection($collection);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CommentRequest $request, Post $post)
    {
        $comment = $post->comment($request->input('body'));

        return CommentResource::make($comment->load('author'));
    }
}
