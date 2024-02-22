<?php

namespace App\Rules;

use App\Models\User;
use App\Models\Media;
use Illuminate\Http\UploadedFile;
use App\Enums\MediaCollectionType;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Contracts\Validation\Rule;

class ValidImage implements Rule, ValidatorAwareRule
{
    /** @var \Illuminate\Validation\Validator  */
    protected $validator;

    protected $isUploadedFile = false;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->isUploadedFile = $value instanceof UploadedFile;

        if ($this->isUploadedFile) {
            return $this->validator->validateImage($attribute, $value);
        }

        $collectionName = data_get($value, 'delete', false)
            ? MediaCollectionType::PORTFOLIO
            : MediaCollectionType::UNASSIGNED;

        return Media::where('id', data_get($value, 'id'))
            ->whereCollectionName($collectionName)
            ->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if ($this->isUploadedFile) {
            return __('validation.image');
        }

        return ':attribute is invalid';
    }

    /**
     * Set the current validator.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return $this
     */
    public function setValidator($validator)
    {
        $this->validator = $validator;

        return $this;
    }
}
