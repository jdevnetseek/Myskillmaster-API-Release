<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Settings\Traits\InteractsWithPlatformSettings;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppVersion extends Model
{
    use HasFactory;
    use InteractsWithPlatformSettings;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'platform',
        'version',
        'upgrade_guide',
        'title',
        'message',
        'store_url',
    ];

    /**
     * Get the store link of the platform, if null use the default platform store link.
     *
     * @param string $value
     * @return void
     */
    public function getStoreUrlAttribute($value)
    {
        return $value ?: $this->getPlatformSettings($this->platform)->store_url;
    }
}
