<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * Disabling auto increment on this model's primary key.
     *
     * @var boolean
     */
    public $incrementing = false;

    /**
     * Disable updated_at on timestamps.
     *
     * @var string
     */
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'token', 'expires_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'token',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'expires_at',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->token = random_int(10000, 99999);
            $model->expires_at = Carbon::now()->addRealMinutes(config('auth.passwords.users.expire'));
        });
    }

    /**
     * The user that this password resets belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
