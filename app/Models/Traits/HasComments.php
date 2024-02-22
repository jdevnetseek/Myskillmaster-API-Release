<?php

namespace App\Models\Traits;

use App\Models\User;
use App\Models\Comment;

trait HasComments
{
    /**
     * List of reponses in a comment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * List of reponses in a comment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function allComments()
    {
        return $this->morphMany(Comment::class, 'model');
    }

    /**
     * A helper in responding to a comment.
     *
     * @param string $body
     * @return Comment
     */
    public function comment(string $body) : Comment
    {
        return $this->commentAsUser(auth()->user(), $body);
    }

    /**
     * A helper in responding to a comment as a user.
     *
     * @param User $user
     * @param string $body
     * @return Comment
     */
    public function commentAsUser(User $user, string $body) : Comment
    {
        $comment = new Comment();
        $comment->author_id = $user->getKey();
        $comment->body      = $body;

        return $this->comments()->save($comment);
    }

    /**
     * Get the first level comments count
     *
     * @return integer
     */
    public function commentsCount() : int
    {
        return $this->comments()->count();
    }

    /**
     * Get all the comments count including response to comments
     *
     * @return integer
     */
    public function allCommentsCount() : int
    {
        return $this->allComments()->count();
    }
}
