<?php

namespace App\Http\Controllers\V1\Job;

use App\Models\Comment;
use App\Models\Job;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CommentRequest;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Resources\CommentResource;
use App\Support\QueryBuilder\AllowedIncludeExtended as AllowedInclude;

class JobCommentController extends Controller
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
    public function index(Request $request, Job $job)
    {
        $collection = QueryBuilder::for(Comment::class)
            ->whereCommentableType($job->getMorphClass())
            ->whereCommentableId($job->getKey())
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
     * @return \Illuminate\Http\Response
     */
    public function store(CommentRequest $request, Job $job)
    {
        $comment = $job->comment($request->input('body'));

        return CommentResource::make($comment->load('author'));
    }
}
