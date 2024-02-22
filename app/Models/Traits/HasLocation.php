<?php

namespace App\Models\Traits;

use App\Models\Location;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

trait HasLocation
{
    /*
    |--------------------------------------------------------------------------
    | This trait help us implement location features faster,
    | Where nearby feature are needed.
    |
    | If Performance is required, it is much better to have the coordinates
    | column directly in your table to avoid joining of tables.
    |
    | This has been tested and have an average 300ms speed on a One hundred
    | thousand data set on a virtual box machine.
    |--------------------------------------------------------------------------
    */

    /**
     * Defines the morph one to locations table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function location()
    {
        return $this->morphOne(Location::class, 'locationable');
    }

    /**
     * Set the Location of the model.
     *
     * @param float $latitude
     * @param float $longitude
     * @return Location
     */
    public function setLocation(float $latitude, float $longitude) : Location
    {
        /**
         * We will store the longitude and latitude to act as cache,
         * so that when we need to get it we don't have to compute it,
         * Saving cpu calculations since Storage is cheap.
         * Same thing with coordinates, we will save the computed point to
         * save some resources.
         */
        return $this->location()->updateOrCreate(
            [
                'locationable_type' => $this->getMorphClass(),
                'locationable_id'   => $this->getKey()
            ],
            [
                'longitude'   => $longitude,
                'latitude'    => $latitude,
                'coordinates' => DB::raw("ST_SRID(Point(${longitude}, ${latitude}), 4326)")
            ]
        );
    }

    /**
     * Safely create a inner join for our locations table.
     *
     * @param Builder $query
     * @param string $table
     * @return void
     */
    private function safelyJoin(Builder $query)
    {
        $table = (new Location)->getTable();

        /**
         * Check if table was already joined
         */
        if (collect($query->getQuery()->joins)->pluck('table')->contains($table)) {
            return;
        }

        $model = $query->getModel();

        $query->join($table, function ($join) use ($model) {
            $join->on($model->location()->qualifyColumn('locationable_id'), $model->getQualifiedKeyName());
            $join->where($model->location()->qualifyColumn('locationable_type'), $model->getMorphClass());
        });

        if (is_null($query->getQuery()->columns)) {
            $query->select($model->qualifyColumn('*'));
        }
    }

    /**
     * Filter results that distance are within the radius
     *
     * @param Builder $query
     * @param float $latitude
     * @param float $longitude
     * @param integer $distanceInMeters
     * @return void
     */
    public function scopeWithinDistanceTo(Builder $query, float $latitude, float $longitude, int $distanceInMeters)
    {
        $this->safelyJoin($query);

        $query->whereRaw(Location::rawSTDistanceQuery($latitude, $longitude) . ' <= ?', [ $distanceInMeters ]);
    }

    /**
     * Appends a distance property to our result.
    *
     * @param Builder $query
     * @param float $latitude
     * @param float $longitude
     * @return void
     */
    public function scopeAppendDistanceTo(Builder $query, float $latitude, float $longitude)
    {
        $this->safelyJoin($query);

        if (is_null($query->getQuery()->columns)) {
            $query->select($query->getModel()->qualifyColumn('*'));
        }

        /**
         * The value of distance is in meters.
         */
        $query->addSelect([ DB::raw(Location::rawSTDistanceQuery($latitude, $longitude) . ' as distance') ]);

        $query->withCasts([
            'distance' => 'float'
        ]);
    }

    /**
     * Appends the coordinates in the select query.
     *
     * @param Builder $query
     * @return void
     */
    public function scopeAppendCoordinates(Builder $query)
    {
        $this->safelyJoin($query);

        if (is_null($query->getQuery()->columns)) {
            $query->select($query->getModel()->qualifyColumn('*'));
        }

        $query->addSelect([
            DB::raw((new Location)->qualifyColumn('latitude')),
            DB::raw((new Location)->qualifyColumn('longitude')),
        ]);

        $query->withCasts([
            'latitude'  => 'float',
            'longitude' => 'float'
        ]);
    }
}
