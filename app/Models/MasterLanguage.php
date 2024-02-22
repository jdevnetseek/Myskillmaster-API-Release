<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterLanguage extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public $timestamps = false;

    public function masterProfile(): BelongsTo
    {
        return $this->belongsTo(MasterProfile::class, 'master_profile_id');
    }
}
