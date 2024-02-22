<?php

namespace App\Http\Controllers\V1;

use App\Models\Category;
use App\Enums\CategoryType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Resources\CategoryResource;

class CategoryController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $collection = QueryBuilder::for(Category::class)
            ->with('icon')
            ->onlyTopParent()
            ->allowedIncludes('subcategories')
            ->allowedFilters(AllowedFilter::exact('type')->default(CategoryType::LESSON))
            ->allowedSorts('label')
            ->defaultSort('label')
            ->get();

        return CategoryResource::collection($collection);
    }
}
