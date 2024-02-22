<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'longitude',
        'latitude',
        'coordinates'
    ];

    /**
     * Builds the raw distance query.
     *
     * @param float $latitude
     * @param float $longitude
     * @return string
     */
    public static function rawSTDistanceQuery(float $latitude, float $longitude): string
    {
        $qualifiedColumn = (new self)->qualifyColumn('coordinates');
        return "ST_Distance(${qualifiedColumn}, ST_SRID(Point(${longitude}, ${latitude}), 4326))";
    }
}
