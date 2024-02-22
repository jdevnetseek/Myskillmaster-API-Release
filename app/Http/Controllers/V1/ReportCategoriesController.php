<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Models\ReportCategories;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Resources\ReportCategoriesResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportCategoriesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $collection = QueryBuilder::for(ReportCategories::class)
            ->when(!$request->has('filter.type'), function ($query) {
                $query->whereNull('type');
            })
            ->allowedFilters(AllowedFilter::exact('type')->default(null))
            ->get();

        return ReportCategoriesResource::collection($collection);
    }
}
