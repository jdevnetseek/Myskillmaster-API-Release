<?php

namespace App\Models\Traits;

use App\Models\User;
use App\Models\Favorite;
use Illuminate\Database\Eloquent\Builder;

trait CanBeFavorite
{
    /**
     * List of reponses in a comment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favoriteable');
    }

    /**
     * Add model to favorites as the current user.
     *
     * @return Favorite
     */
    public function favorite() : Favorite
    {
        return $this->favoriteAsUser(auth()->user());
    }


    /**
     * Add a computed `is_favorite` column to models result
     *
     * @param Builder $query
     * @param User $user
     * @return void
     */
    public function scopeAppendIsFavorite(Builder $query)
    {
        $query->appendIsFavoriteAsUser(auth()->user());
    }

    /**
     * Add a computed `is_favorite` column to models result
     *
     * @param Builder $query
     * @param User $user
     * @return void
     */
    public function scopeAppendIsFavoriteAsUser(Builder $query, User $user)
    {
        $query->addSelect(['is_favorite' => Favorite::selectRaw('count(id) as count')
            ->whereFavoriteableType($this->getMorphClass())
            ->whereColumn((new Favorite)->qualifyColumn('favoriteable_id'), $this->qualifyColumn('id'))
            ->whereUserId($user->getKey())
            ->take(1)
        ]);

        $query->withCasts(['is_favorite' => 'boolean']);
    }

    /**
     * Filter favoritable where favorite/liked/saved by user
     *
     * @param Builder $query
     * @return void
     */
    public function scopeOnlyFavoritesBy(Builder $query, User $user)
    {
        $query->whereHas('favorites', function ($query) use ($user) {
            $query->whereUserId($user->getKey());
        });
    }

    /**
     * Add model to favorites as the provided user.
     *
     * @param User $user
     * @return Favorite
     */
    public function favoriteAsUser(User $user) : Favorite
    {
        return $this->favorites()->firstOrCreate([ 'user_id' => $user->getKey() ]);
    }

    /**
     * Remove a model to favorite as current user.
     *
     * @return void
     */
    public function unfavorite() : void
    {
        $this->unfavoriteAsUser(auth()->user());
    }

    /**
     * Remove a model to favorite as provided user.
     *
     * @param User $user
     * @return void
     */
    public function unfavoriteAsUser(User $user) : void
    {
        $this->favorites()->where('user_id', $user->getKey())->delete();
    }

    /**
     * Check if users favorites the model.
     *
     * @return boolean
     */
    public function isFavorite() : bool
    {
        return $this->isFavoriteAs(auth()->user());
    }

     /**
     * Check if users favorites the model.
     *
     * @param User $user
     * @return boolean
     */
    public function isFavoriteAs(User $user) : bool
    {
        return $this->favorites()->whereUserId($user->getKey())->exists();
    }

    /**
     * Get the favorites count
     *
     * @return integer
     */
    public function favoritesCount() : int
    {
        return $this->favorites()->count();
    }
}
