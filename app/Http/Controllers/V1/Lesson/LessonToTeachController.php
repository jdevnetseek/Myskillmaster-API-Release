<?php

namespace App\Http\Controllers\V1\Lesson;

use App\Http\Controllers\Controller;
use App\Http\Resources\LessonToTeachResource;
use App\Models\LessonSchedule;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class LessonToTeachController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Handle the incoming request.
     * Get the current lessons that the master need to teach
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $collections = QueryBuilder::for(LessonSchedule::class)
            ->where(function ($query) {
                $query->upcoming()
                    ->orWhere
                    ->ongoing();
            })
            ->hasMasterLesson($request->user())
            ->hasStudentNotConfirmedEnrollment($request->user())
            ->with($this->withRelationships())
            ->orderBy('schedule_start', 'desc')
            ->paginate($request->perPage());

        return LessonToTeachResource::collection($collections);
    }

    public function show(Request $request, $schedule)
    {
        $collection = QueryBuilder::for(LessonSchedule::class)
            ->where('id', $schedule)
            ->hasMasterLesson($request->user())
            ->hasStudentNotConfirmedEnrollment($request->user())
            ->with($this->withRelationships())
            ->firstOrFail();

        return LessonToTeachResource::make($collection);
    }

    private function withRelationships()
    {
        return [
            'masterLesson',
            'masterLesson.cover',
            'masterLesson.place',
            'masterLesson.place.country',
            'masterLesson.place.state',
            'masterProfile',
            'studentProfile',
            'lessonEnrollments'
        ];
    }
}
