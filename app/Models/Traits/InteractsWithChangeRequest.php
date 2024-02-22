<?php

namespace App\Models\Traits;

use Exception;
use App\Models\ChangeRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

trait InteractsWithChangeRequest
{
    /**
     * Define a polymorphic one-to-many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function changedRequests()
    {
        return $this->morphMany(ChangeRequest::class, 'changeable');
    }

    /**
     * Create a change request for field name.
     *
     * @param string $fieldName
     * @param mixed $newValue
     * @param mixed $token
     * @return ChangeRequest
     */
    public function changeRequestFor(string $fieldName, $newValue, $token = null) : ChangeRequest
    {
        if (!array_key_exists($fieldName, $this->attributes)) {
            throw new Exception("The ${fieldName} does not exists!");
        }

        return $this->changedRequests()->updateOrCreate(
            [ 'field_name'      => $fieldName, ],
            [
                'from'            => $this->attributes[$fieldName],
                'to'              => $newValue,
                'token'           => !is_null($token) ? Hash::make($token) : null
            ]
        );
    }

    /**
     * Get The change request for field name.
     *
     * @param string $fieldName
     * @return ChangeRequest|null
     */
    public function getChangeRequestFor(string $fieldName) : ?ChangeRequest
    {
        return $this->changedRequests()
            ->whereFieldName($fieldName)
            ->first();
    }

    /**
     * Apply the change request for a field name
     *
     * @param string $fieldName
     * @return void
     */
    public function applyChangeRequest($changeRequest)
    {
        DB::transaction(function () use ($changeRequest) {
            $this->setAttribute($changeRequest->field_name, $changeRequest->to);
            $this->save();

            $this->changedRequests()
                ->whereId($changeRequest->id)
                ->delete();
        });
    }
}
