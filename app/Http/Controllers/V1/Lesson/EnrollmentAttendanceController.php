<?php

namespace App\Http\Controllers\V1\Lesson;

use App\Http\Controllers\Controller;
use App\Models\LessonEnrollment;
use Illuminate\Http\Request;

class EnrollmentAttendanceController extends Controller
{
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
        $request->validate(['reference_code' => 'required:exists:lesson_enrollments,reference_code']);

        $enrollment = LessonEnrollment::where('reference_code', $request->input('reference_code'))
            ->where('student_id', $request->user()->id)
            ->firstOrFail();

        $enrollment->update([
            'is_student_attended' => false
        ]);

        return response()->json(['message' => 'Attendance confirmed']);
    }
}
