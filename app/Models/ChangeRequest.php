<?php

namespace App\Models;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use App\Support\Faker\BypassCodeValidator;

class ChangeRequest extends Model
{
    use BypassCodeValidator;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'change_requests';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'from',
        'to',
        'token',
        'field_name'
    ];

    /**
     * Define a polymorphic, inverse one-to-one or many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function changeable()
    {
        return $this->morphTo();
    }

    /**
     * Verify Token
     */
    public function isTokenValid($token) : bool
    {
        /** on debug mode, allow bypass for token validation */
        if ($this->isUsingBypassCode($token)) {
            return true;
        }

        return Hash::check($token, $this->token);
    }
}
