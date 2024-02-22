<?php

namespace App\Http\Controllers\V1\Admin\Report;

use App\Models\User;
use App\Models\Report;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Enums\ReportCategoryType;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Resources\UserReportResource;
use Spatie\QueryBuilder\Filters\FiltersScope;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
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
    public function __invoke(Request $request)
    {
        $collection = QueryBuilder::for(Report::class)
            ->allowedIncludes('attachments', 'reason', 'reporter')
            ->allowedSorts('created_at')
            ->allowedFilters([
                AllowedFilter::scope('search')->ignore(null)
            ])
            ->with('reportable')
            ->hasReportType(ReportCategoryType::USER)
            ->defaultSort('-created_at')
            ->paginate();

        return UserReportResource::collection($collection);
    }
}
