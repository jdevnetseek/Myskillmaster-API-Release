<?php

namespace App\Models;

use App\Models\Traits\HasComments;
use App\Models\Traits\CanBeReported;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory;

    use HasComments {
        comments as responses;
        comment as respond;
        commentAsUser as respondAsUser;
    }

    use CanBeReported, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'body',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        // register model observer
        static::creating(function (Comment $comment) {
            /**
             * model_type, model_id acts as a referece to the top most model
             * where the comments was made.
             */
            if ($comment->commentable_type === $comment->getMorphClass()) {
                /**
                 * When a comment is a response to a comment, it will be considered
                 * a nested comment. We will then get the parents model_type and model_id
                 * for reference to top level model.
                 */
                $comment->model_type = $comment->commentable->model_type;
                $comment->model_id   = $comment->commentable->model_id;
            } else {
                /**
                 * This is a top level comment. We will store the type and id
                 * to our model_type and model_id for the nested comments to use
                 * to have a reference to our top parent model.
                 */
                $comment->model_type = $comment->commentable_type;
                $comment->model_id   = $comment->commentable_id;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Define a polymorphic, inverse one-to-one or many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function commentable()
    {
        return $this->morphTo();
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Checks if the comment is owned by the user.
     *
     * @param Builder $query
     * @param integer $userId
     * @return boolean
     */
    public function isOwner(User $user) : bool
    {
        return $this->author_id === $user->getKey();
    }
}
