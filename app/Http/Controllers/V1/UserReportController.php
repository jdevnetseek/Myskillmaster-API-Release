<?php

namespace App\Http\Controllers\V1;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportRequest;
use App\Http\Resources\UserReportResource;

class UserReportController extends Controller
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
    public function __invoke(ReportRequest $request, User $user)
    {
        $report = $user->report(
            $request->input('reason_ids'),
            $request->input('description'),
            $request->file('attachments', [])
        );

        return UserReportResource::make($report->load('reportable', 'attachments'));
    }
}
