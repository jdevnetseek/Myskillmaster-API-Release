<?php

namespace App\Http\Controllers\V1\Lesson;

use App\Http\Controllers\Controller;
use App\Http\Resources\MasterPastLessonResource;
use App\Models\LessonSchedule;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class MasterPastLessonController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $collections = QueryBuilder::for(LessonSchedule::class)
            ->where('schedule_end', '<', now())
            ->hasMasterLesson($request->user())
            ->hasStudentEnrollment($request->user())
            ->with($this->withRelationships())
            ->orderBy('schedule_start', 'desc')
            ->paginate($request->perPage());

        return MasterPastLessonResource::collection($collections);
    }

    public function show(Request $request, $schedule)
    {
        $collection = QueryBuilder::for(LessonSchedule::class)
            ->where('id', $schedule)
            ->where('schedule_end', '<', now())
            ->hasMasterLesson($request->user())
            ->hasStudentEnrollment($request->user())
            ->with($this->withRelationships())
            ->firstOrFail();

        return new MasterPastLessonResource($collection);
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
