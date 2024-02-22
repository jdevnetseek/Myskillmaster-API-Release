<?php

namespace App\Http\Controllers\V1\Lesson;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentPastLessonResource;
use App\Models\LessonEnrollment;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class StudentPastLessonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $collections = QueryBuilder::for(LessonEnrollment::class)
            ->asStudent($request->user())
            ->hasPastLessonSchedule()
            ->notCancelled()
            ->ratedOrAttended()
            ->with($this->withRelationships())
            ->paginate($request->perPage());

        return StudentPastLessonResource::collection($collections);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $referenceCode)
    {
        $collection = QueryBuilder::for(LessonEnrollment::class)
            ->where('reference_code', $referenceCode)
            ->asStudent($request->user())
            ->hasPastLessonSchedule()
            ->notCancelled()
            ->ratedOrAttended()
            ->with($this->withRelationships())
            ->firstOrFail();

        return new StudentPastLessonResource($collection);
    }

    private function withRelationships()
    {
        return [
            'schedule',
            'lesson',
            'lesson.cover',
            'lesson.place',
            'master',
            'master.address.country',
            'master.address.state',
            'master.masterProfile',
        ];
    }
}
