<?php

namespace App\Models;

use App\Models\Traits\Rateable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;
    use Rateable;

    protected $fillable = [
        'user_id'
    ];
}
