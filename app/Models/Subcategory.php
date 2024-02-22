<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subcategory extends Category
{
    use HasFactory;
    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('categoryRule', function (Builder $builder) {
            $builder->whereNotNull('parent_id');
        });

        static::creating(function (Subcategory $model) {
            throw_if(is_null($model->parent_id), new Exception('Subcategory does not contain a parent.'));
            // If no type was provided use the parent type
            $model->type = $model->type ?? $model->category->type;
        });
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
}
