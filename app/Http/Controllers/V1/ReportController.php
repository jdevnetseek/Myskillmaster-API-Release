<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Actions\SubmitReport;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use App\Enums\ReportCategoryType;
use App\Http\Controllers\Controller;

class ReportController extends Controller
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
    public function __invoke(Request $request, SubmitReport $submitReport)
    {
        $request->validate([
            'report_type'   => ['bail', 'required', 'string', Rule::in(ReportCategoryType::getValues())],
            'report_id'     => ['bail', 'required'],
            'reason_id'     => ['bail', 'required', 'exists:report_categories,id'],
            'description'   => ['bail', 'nullable', 'string'],
            'attachments'   => ['bail', 'nullable', 'array'],
            'attachments.*' => ['image'],
            'photos'        => ['bail', 'nullable', 'array'], // Added to fix issue, since both mobile platform uses photos
            'photos.*'      => ['image']
        ]);

        $submitReport->execute(
            $request->input('report_type'),
            $request->input('report_id'),
            $request->input('reason_id'),
            $request->input('description'),
            $request->has('photos') ? $request->file('photos', []) : $request->file('attachments', [])
        );

        return response()->json([], Response::HTTP_OK);
    }
}
