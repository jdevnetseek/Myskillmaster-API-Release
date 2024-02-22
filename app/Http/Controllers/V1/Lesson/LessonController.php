<?php

namespace App\Http\Controllers\V1\Lesson;

use App\Http\Controllers\Controller;
use App\Http\Resources\MasterLessonResource;
use App\Models\MasterLesson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class LessonController extends Controller
{
    /**
     * Get a list of master lessons based on a search term
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $collections = QueryBuilder::for(MasterLesson::class)
            ->onlyMasterLessons()
            ->allowedIncludes(['cover', 'user.masterProfile', 'user.address', 'place'])
            ->allowedFilters([
                AllowedFilter::scope('search')->default(false)
            ])
            ->with('schedules', 'schedules.lessonEnrollments')
            ->defaultSort('title')
            ->paginate($request->perPage());

        return MasterLessonResource::collection($collections);
    }

    /**
     * Get a paginated list of popular master lessons.
     *
     * @param Request $request
     * @return MasterLessonResource
     */
    public function popular(Request $request)
    {
        $collections = QueryBuilder::for(MasterLesson::class)
            ->popularLesson()
            ->inRandomOrder()
            ->with('user', 'user.masterProfile', 'user.address', 'place')
            ->allowedIncludes(['cover', 'user.masterProfile', 'user.address', 'place'])
            ->defaultSort('title')
            ->paginate($request->perPage());

        return MasterLessonResource::collection($collections);
    }
}
