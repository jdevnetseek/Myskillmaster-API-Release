<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Place extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'city',
        'state_id',
        'country_id',
    ];

    public $timestamps = false;

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    // Mutators and Accessors

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function city(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucwords($value)
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function formattedAddress(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) =>
            ucwords($attributes['city']) . ', ' . strtoupper($this->state->short_name)
        );
    }
}
