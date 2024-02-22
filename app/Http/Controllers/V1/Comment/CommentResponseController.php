<?php

namespace App\Http\Controllers\V1\Comment;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CommentRequest;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Resources\CommentResource;
use App\Support\QueryBuilder\AllowedIncludeExtended as AllowedInclude;

class CommentResponseController extends Controller
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
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Comment $comment)
    {
        $collection = QueryBuilder::for(Comment::class)
            ->whereCommentableType($comment->getMorphClass())
            ->whereCommentableId($comment->getKey())
            ->allowedIncludes([
                'author',
                'responsesCount',
                AllowedInclude::relationshipBuilder('responses', function ($query) {
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
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function store(CommentRequest $request, Comment $comment)
    {
        $response = $comment->respond($request->input('body'));

        return CommentResource::make($response->load('author'));
    }
}
