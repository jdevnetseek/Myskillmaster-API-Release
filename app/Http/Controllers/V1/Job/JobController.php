<?php

namespace App\Http\Controllers\V1\Job;

use App\Models\Job;
use App\Models\Category;
use App\Enums\CategoryType;
use App\Jobs\SyncJobPhotos;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\JobResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportRequest;
use App\Actions\SyncModelAttachments;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Requests\JobStoreRequest;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Requests\JobUpdateRequest;
use Spatie\QueryBuilder\AllowedInclude;
use App\Http\Resources\CategoryResource;

class JobController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->except('categories');
        $this->authorizeResource(Job::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $collection = QueryBuilder::for(Job::class)
            ->appendIsFavorite()
            ->appendIsReported()
            ->allowedFilters(
                AllowedFilter::scope('search')->ignore(null),
                AllowedFilter::exact('category_id')->ignore(null),
                // AllowedFilter::scope('suburb', 'filterBySuburb')->ignore(null),
                AllowedFilter::exact('subcategory_id')->ignore(null),
                AllowedFilter::exact('author_id')->ignore(null),
                AllowedFilter::scope('only_popular')->ignore(null, false),
                AllowedFilter::callback('only_favorited', function ($query, $value) use ($request) {
                    $query->onlyFavoritesBy($request->user());
                })->ignore(null, false),
                AllowedFilter::callback('within_distance_to', function ($query, $value) {
                    abort_if(count($value) != 3, 422, 'Invalid arguments for within_distance_to.');
                    $query->appendCoordinates();
                    $query->appendDistanceTo($value[0], $value[1]);
                    $query->withinDistanceTo($value[0], $value[1], $value[2]);
                })
            )
            ->allowedIncludes([
                'author', 'author.avatar', 'photos', 'favoritesCount', 'category', 'subcategory',
                AllowedInclude::count('commentsCount', 'allComments')
            ])
            ->allowedSorts('created_at')
            ->defaultSort('-created_at')
            ->when(!is_null($request->input('filter.within_distance_to')), function ($query) {
                $query->reorder('distance');
            })
            ->when(!is_null($request->input('filter.search')), function ($query) {
                // Remove any kind of sorting when search filter is found.
                // So that the result will be sorted using the highest rank
                // given by full text search.
                $query->reorder();
            })
            ->paginate($request->perPage());

        return JobResource::collection($collection);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(JobStoreRequest $request, SyncModelAttachments $syncAttachments)
    {
        return DB::transaction(function () use ($request, $syncAttachments) {
            /** @var Job */
            $job = $request->user()->jobs()->create($request->validated());

            if ($request->has('latitude') && $request->has('longitude')) {
                $job->setLocation($request->input('latitude'), $request->input('longitude'));
            }

            $syncAttachments->execute($job, collect($request->file('photos')));

            return JobResource::make($job->load('photos'));
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Job $job)
    {
        $job->load('author', 'photos', 'category', 'subcategory');

        $job->is_favorite = $job->isFavorite();
        $job->is_reported = $job->isReported();

        return JobResource::make($job);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(JobUpdateRequest $request, SyncModelAttachments $syncAttachments, Job $job)
    {
        return DB::transaction(function () use ($request, $syncAttachments, $job) {
            $job->update($request->validated());

            if ($request->has('latitude') && $request->has('longitude')) {
                $job->setLocation($request->input('latitude'), $request->input('longitude'));
            }

            $syncAttachments->execute($job, collect($request->input('photos')));

            return JobResource::make($job->load('photos'));
        });
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Job  $job
     * @return \Illuminate\Http\Response
     */
    public function destroy(Job $job)
    {
        $job->delete();

        return response()->json([], Response::HTTP_OK);
    }

    /**
     * Handles a request in adding jobs to favorite.
     *
     * @param Job $job
     * @return void
     */
    public function favorite(Job $job)
    {
        $job->favorite();

        return response()->json([], Response::HTTP_OK);
    }

    /**
     * Handles a request in removing jobs from favorite.
     *
     * @param Job $job
     * @return void
     */
    public function unfavorite(Job $job)
    {
        $job->unfavorite();

        return response()->json([], Response::HTTP_OK);
    }

    /**
     * Handles Request when a job has been reported.
     *
     * @deprecated 1.6 use /report
     *
     * @param ReportRequest $request
     * @param Job $job
     * @return void
     */
    public function report(ReportRequest $request, Job $job)
    {
        $job->report(
            $request->input('reason_ids'),
            $request->input('description'),
            $request->file('attachments', [])
        );

        return response()->json([], Response::HTTP_OK);
    }

    /**
     * List all job categories.
     *
     * @return void
     */
    public function categories()
    {
        $collection = QueryBuilder::for(Category::class)
            ->onlyTopParent()
            ->whereType(CategoryType::JOB)
            ->allowedIncludes('subcategories')
            ->allowedSorts('label')
            ->defaultSort('label')
            ->get();

        return CategoryResource::collection($collection);
    }
}
