<?php

namespace App\Http\Controllers\V1\Lesson;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleCheckerRequest;
use App\Http\Requests\StoreLessonSchedule;
use App\Http\Resources\LessonScheduleResource;
use App\Models\LessonSchedule;
use App\Models\MasterLesson;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LessonScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(StoreLessonSchedule $request)
    {
        $lessonSchedule = new LessonSchedule();
        $lessonSchedule->master_lesson_id = $request->master_lesson_id;

        $this->authorize('create', $lessonSchedule);

        $schedule = DB::transaction(function () use ($request, $lessonSchedule) {

            $lessonScheduleArray = $request->get('lesson_schedules');

            $schedules = [];

            foreach ($lessonScheduleArray as $key => $lessonSchedule) {
                $schedule = Carbon::parse($lessonSchedule['schedule_start'], $request->input('timezone'));
                $scheduleUtc = $schedule->setTimezone('UTC');
                $scheduleEndUtc = $scheduleUtc->copy()->addHours($lessonScheduleArray[$key]['duration_in_hours']);

                $schedules[$key]['schedule_start'] = Carbon::instance($scheduleUtc);
                $schedules[$key]['schedule_end'] = Carbon::instance($scheduleEndUtc);
                $schedules[$key]['duration_in_hours'] = $lessonScheduleArray[$key]['duration_in_hours'];
            }

            $masterLesson = MasterLesson::find($request->master_lesson_id);
            $masterLesson->schedules()->createMany($schedules);

            return $masterLesson->schedules;
        });

        return LessonScheduleResource::collection($schedule->fresh());
    }

    public function destroy(LessonSchedule $lessonSchedule)
    {
        $this->authorize('delete', $lessonSchedule);

        $lessonSchedule->delete();

        return response()->json([
            'message' => 'Lesson schedule successfully deleted.'
        ], 200);
    }

    public function duplicateChecker(ScheduleCheckerRequest $request)
    {
        if ($request->validated()) {
            return response()->json([
                'message' => 'No duplicate schedule start'
            ], 200);
        }
    }
}
