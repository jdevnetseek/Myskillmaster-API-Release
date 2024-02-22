<?php

namespace App\Http\Resources\Traits;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;

trait HasMappedResource
{
    /*
    |--------------------------------------------------------------------------
    | HasMappedResource
    |--------------------------------------------------------------------------
    |
    | This trait handles the mapping of resource based on the model provided.
    | It is useful when dealing with a polymorphic relationship and you want
    | to avoid implementing multiple conditional statement.
    |
    */

    /**
     * The list of Models class to be mapped to a resource
     *
     * @return array
     */
    abstract protected function mappedResource() : array;

    /**
     * Use to get the Models Mapped resource.
     *
     * @param Model $model
     * @return JsonResource
     */
    private function getMappedResource(Model $model) : JsonResource
    {
        $class = get_class($model);

        $resource = data_get($this->mappedResource(), $class);

        throw_if(blank($resource), Exception::class, 'No resource was mapped for ' . $class);

        return new $resource($model);
    }

}
