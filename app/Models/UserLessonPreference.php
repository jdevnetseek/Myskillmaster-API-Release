<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserLessonPreference extends Pivot
{
    protected $table = 'user_lesson_preferences';
}
