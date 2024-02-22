<?php

namespace App\Rules;

use Illuminate\Support\Str;
use App\Support\ValidatesPhone;
use Illuminate\Database\Query\Builder;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class UniquePhoneNumber implements Rule
{
    use ValidatesPhone;

    /** @var Builder */
    private $query;

    /** @var string */
    private $column;

    /** @var string */
    private $attribute;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($abstract, string $column = "phone_number")
    {
        if ($abstract instanceof EloquentBuilder) {
            $this->query = $abstract->getQuery();
        } elseif ($abstract instanceof Builder) {
            $this->query = $abstract;
        } else {
            $this->query = (new $abstract)->query();
        }

        $this->column = $column;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->attribute = $attribute;

        return !$this->query->where($this->column, $this->cleanPhoneNumber($value))->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.unique', [':attribute' => $this->attribute]);
    }
}
