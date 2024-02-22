<?php

namespace App\Models\Traits;

use App\Models\Category;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasLessonPreferences
{
    public function lessonPreferences(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'user_lesson_preferences'
        );
    }

    /**
     * Set lesson preferences
     * Remove all categories that are not provided in $categoryIds param
     *
     * @param array $categoryIds
     * @return void
     */
    public function setLessonPreferences(array $categoryIds)
    {
        $this->lessonPreferences()->sync($categoryIds);
    }
}
