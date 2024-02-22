<?php

namespace App\Models\Traits;

use App\Models\Like;
use App\Enums\LikeType;

trait HasLikesAndDislikes
{
    /**
     * Collection of likes and dislikes on this record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function likesAndDislikes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    /**
     * Collection of likes on this record
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function likes()
    {
        return $this->likesAndDislikes()->where('type_id', LikeType::LIKE);
    }

    /**
     * Collection of dislikes on this record
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function dislikes()
    {
        return $this->likesAndDislikes()->where('type_id', LikeType::DISLIKE);
    }

    /**
     * Model likesCount attribute
     *
     * @return int
     */
    public function getLikesCountAttribute() : int
    {
        return $this->likes->count() || 0;
    }

    /**
     * Model dislikesCount attribute
     *
     * @return int
     */
    public function getDislikesCountAttribute() : int
    {
        return $this->dislikes->count() || 0;
    }

    /**
     * Add like on this record by the given user
     *
     * @param mixed $userId
     * @return void
     */
    public function like($userId = null)
    {
        $this->addLikeTo(LikeType::LIKE, $userId);
    }

    /**
     * Add dislike on this record by the given user
     *
     * @param mixed $userId
     * @return void
     */
    public function dislike($userId = null)
    {
        $this->addLikeTo(LikeType::DISLIKE, $userId);
    }

    /**
     * Remove like on this record by the given user
     *
     * @param mixed $userId
     * @return void
     */
    public function unlike($userId = null)
    {
        $this->removeLikeFrom(LikeType::LIKE, $userId);
    }

    /**
     * Remove dislike on this record by the given user
     *
     * @param mixed $userId
     * @return void
     */
    public function undislike($userId = null)
    {
        $this->removeLikeFrom(LikeType::DISLIKE, $userId);
    }

    /**
     * Add likeable model by given user
     *
     * @param string $type
     * @param mixed $userId
     * @return void
     */
    public function addLikeTo($type, $userId = null)
    {
        $userId = $this->loggedInUserId($userId);

        if ($userId) {
            $like = $this->likesAndDislikes()
                ->where('user_id', '=', $userId)
                ->first();

            if (!$like) {
                $like = new Like();
                $like->user_id = $userId;
                $like->type_id = $type;
                $this->likesAndDislikes()->save($like);

                return;
            }

            if ($like->type_id == $type) {
                return;
            }

            $like->delete();

            $like = new Like();
            $like->user_id = $userId;
            $like->type_id = $type;
            $this->likesAndDislikes()->save($like);
        }
    }

    /**
     * Remove likeable model by given user
     *
     * @param string $type
     * @param mixed $userId
     * @return void
     */
    public function removeLikeFrom($type, $userId)
    {
        $userId = $this->loggedInUserId($userId);

        $like = $this->likesAndDislikes()
            ->where('user_id', '=', $userId)
            ->where('type_id', '=', $type)
            ->first();

        if (!$like) {
            return;
        }

        $like->delete();
    }

    /**
     * Did the given user like this model
     *
     * @param mixed $userId
     * @return bool
     */
    public function liked($userId = null)
    {
        return $this->hasLikeable(LikeType::LIKE, $userId);
    }

    /**
     * Did the given user dislike this model
     *
     * @param mixed $userId
     * @return bool
     */
    public function disliked($userId = null)
    {
        return $this->hasLikeable(LikeType::DISLIKE, $userId);
    }

    /**
     * Did the currently logged in user like this model
     *
     * @return bool
     */
    public function getLikedAttribute()
    {
        return $this->liked();
    }

    /**
     * Did the currently logged in user dislike this model
     *
     * @return bool
     */
    public function getDislikedAttribute()
    {
        return $this->disLiked();
    }

    /**
     * Did the given user like or dislike this model
     *
     * @param string $type
     * @param mixed $userId
     * @return bool
     */
    public function hasLikeable($type, $userId = null)
    {
        $userId = $this->loggedInUserId($userId);

        if (!$userId) {
            return false;
        }

        return $this->likesAndDislikes()
            ->where('user_id', '=', $userId)
            ->where('type_id', '=', $type)
            ->exists();
    }

    /**
     * Get ID of the current logged in user
     *
     * @param mixed $userId
     * @return mixed
     */
    private function loggedInUserId($userId)
    {
        if (is_null($userId)) {
            $userId = auth()->id();
        }

        return $userId;
    }
}