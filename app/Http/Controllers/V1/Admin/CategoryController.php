<?php

namespace App\Http\Controllers\V1\Admin;

use App\Models\Category;
use App\Enums\CategoryType;
use App\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreRequest;
use App\Http\Requests\Category\UpdateRequest;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('user.role:' . Role::ADMIN . '|' . Role::SUPER_ADMIN);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = QueryBuilder::for(Category::class)
            ->with('icon')
            ->allowedFilters(
                AllowedFilter::scope('search'),
                AllowedFilter::exact('type')
            )
            ->allowedSorts('label')
            ->defaultSort('label')
            ->paginate(request()->perPage());

        return CategoryResource::collection($categories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        /** @var App\Models\Category */
        $category = DB::transaction(function () use ($request) {
            $category = Category::firstOrCreate([
                'label' => $request->input('label'),
                'type' => $request->input('type', $request->input('type', CategoryType::LESSON))
            ]);

            if ($request->has('keywords')) {
                $category->update([
                    'keywords' => $request->input('keywords')
                ]);
            }

            if ($request->hasFile('icon')) {
                $category->setIcon($request->file('icon'));
            }

            return $category;
        });

        return CategoryResource::make($category);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        return CategoryResource::make($category->load('icon'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, Category $category)
    {
        $category = DB::transaction(function () use ($request, $category) {
            $category->update($request->validated());

            if ($request->hasFile('icon')) {
                $category->setIcon($request->file('icon'));
            }

            return $category->load('icon');
        });

        return CategoryResource::make($category);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        abort_if(
            $category->lessons()->exists(),
            Response::HTTP_BAD_REQUEST,
            __('error_messages.category.delete_has_lesson')
        );

        $category->delete();

        return $this->respondWithEmptyData();
    }
}
