<?php

namespace App\Http\Controllers\V1\Lesson;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportRequest;
use App\Http\Resources\LessonReportResource;
use App\Models\MasterLesson;

class LessonReportController extends Controller
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
    public function __invoke(ReportRequest $request, MasterLesson $lesson)
    {
        $report = $lesson->report(
            $request->input('reason_ids'),
            $request->input('description'),
            $request->file('attachments', [])
        );

        return LessonReportResource::make($report->load('reportable', 'attachments', 'reasons'));
    }
}
