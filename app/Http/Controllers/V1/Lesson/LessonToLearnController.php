<?php

namespace App\Http\Controllers\V1\Lesson;

use App\Http\Controllers\Controller;
use App\Http\Resources\LessonToLearnResource;
use App\Models\LessonEnrollment;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class LessonToLearnController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Handle the incoming request.
     * Get the current lessons that the student need to learn
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $collections = QueryBuilder::for(LessonEnrollment::class)
            ->notCancelled()
            ->notRatedOrAttended()
            ->asStudent($request->user())
            ->whereHas('schedule', fn ($query) => $query->upcoming())
            ->with($this->withRelationships())
            ->paginate($request->perPage());

        return LessonToLearnResource::collection($collections);
    }

    public function show(Request $request, $referenceCode)
    {
        $collection = QueryBuilder::for(LessonEnrollment::class)
            ->notCancelled()
            ->notRated()
            ->attendanceNotConfirmed()
            ->asStudent($request->user())
            ->where('reference_code', $referenceCode)
            ->with($this->withRelationships())
            ->firstOrFail();

        return LessonToLearnResource::make($collection);
    }

    public function finishedLessons(Request $request)
    {
        $collections = QueryBuilder::for(LessonEnrollment::class)
            ->notCancelled()
            ->notRated()
            ->attendanceNotConfirmed()
            ->asStudent($request->user())
            ->whereHas('schedule', fn ($query) => $query->completed())
            ->with($this->withRelationships())
            ->paginate($request->perPage());

        return LessonToLearnResource::collection($collections);
    }

    private function withRelationships()
    {
        return [
            'schedule',
            'lesson',
            'lesson.cover',
            'lesson.place',
            'master.address.country',
            'master.address.state',
            'master.masterProfile',
        ];
    }
}
