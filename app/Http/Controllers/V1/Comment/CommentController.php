<?php

namespace App\Http\Controllers\V1\Comment;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportRequest;
use App\Http\Requests\CommentRequest;
use App\Http\Resources\CommentResource;

class CommentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->authorizeResource(Comment::class);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Comment $comment)
    {
        return CommentResource::make($comment->load('author'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CommentRequest $request, Comment $comment)
    {
        $comment->update($request->validated());

        return CommentResource::make($comment->load('author'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();

        return response()->json([], Response::HTTP_OK);
    }

    /**
     * Handles Request when a comment has been reported.
     *
     * @deprecated 1.6 use /report
     *
     * @param ReportRequest $request
     * @return void
     */
    public function report(ReportRequest $request, Comment $comment)
    {
        $comment->report(
            $request->input('reason_ids'),
            $request->input('description'),
            $request->file('attachments', [])
        );

        return response()->json([], Response::HTTP_OK);
    }
}
