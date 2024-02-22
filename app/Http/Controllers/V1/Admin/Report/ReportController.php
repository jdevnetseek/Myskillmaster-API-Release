<?php

namespace App\Http\Controllers\V1\Admin\Report;

use App\Enums\Role;
use App\Enums\ReportStatus;
use App\Models\Job;
use App\Models\Post;
use App\Models\User;
use App\Models\Report;
use App\Models\Comment;
use App\Models\Product;
use App\Models\ReportCategories;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Resources\ReportResource;
use App\Http\Resources\ReportCategoriesResource;
use App\Models\MasterLesson;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('user.role:' . Role::ADMIN . '|' . Role::SUPER_ADMIN);
    }

    public function index(Request $request)
    {
        $collection = QueryBuilder::for(Report::class)
            ->with(['reportable' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    User::class    => ['avatar'],
                    Post::class    => ['author', 'photo'],
                    Product::class => ['seller', 'photos'],
                    Job::class     => ['author', 'photos'],
                    Comment::class => ['author'],
                    MasterLesson::class => ['user'],
                ]);
            }])
            ->allowedIncludes('attachments', 'reasons', 'reporter')
            ->allowedSorts('created_at')
            ->allowedFilters([
                AllowedFilter::scope('search')->ignore(null),
                AllowedFilter::scope('lesson', 'searchLesson')->ignore(null),
                AllowedFilter::scope('type', 'hasReportType'),
                AllowedFilter::scope('category')->ignore(null),
                AllowedFilter::scope('reason')->ignore(null),
                AllowedFilter::scope('status')->ignore(null),
                AllowedFilter::scope('date_reported')->ignore(null),
                AllowedFilter::exact('reported_by'),
                AllowedFilter::callback('lesson_id', function (Builder $query, $value) {
                    $query->where('reportable_type', MasterLesson::class)->where('reportable_id', $value);
                })
            ])
            ->defaultSort('-created_at')
            ->paginate();

        return ReportResource::collection($collection);
    }

    public function getReportCategories()
    {
        $collection = QueryBuilder::for(ReportCategories::class)->get();

        return ReportCategoriesResource::collection($collection);
    }

    public function show(Report $report)
    {
        $report->load(['attachments', 'reasons', 'reporter']);

        $report->load(['reportable' => function (MorphTo $morphTo) {
            $morphTo->morphWith([
                User::class    => ['avatar'],
                Post::class    => ['author', 'photo'],
                Product::class => ['seller', 'photos'],
                Job::class     => ['author', 'photos'],
                Comment::class => ['author'],
                MasterLesson::class => ['user'],
            ]);
        }]);

        return ReportResource::make($report);
    }

    public function update(Report $report, Request $request)
    {
        $request->validate([
            'status' => ['required', new EnumValue(ReportStatus::class)]
        ]);

        $report->update(['status' => $request->status]);

        $report->fresh();

        $report->load(['attachments', 'reasons', 'reporter']);

        $report->load(['reportable' => function (MorphTo $morphTo) {
            $morphTo->morphWith([
                User::class    => ['avatar'],
                Post::class    => ['author', 'photo'],
                Product::class => ['seller', 'photos'],
                Job::class     => ['author', 'photos'],
                Comment::class => ['author'],
                MasterLesson::class => ['user'],
            ]);
        }]);

        return ReportResource::make($report);
    }
}
