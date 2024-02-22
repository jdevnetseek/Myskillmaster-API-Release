<?php

namespace App\Models\Traits;

use App\Enums\ReportStatus;
use App\Models\MasterLesson;
use App\Models\Report;

trait InteractsWithReport
{
    public function reportedLessons()
    {
        return $this->hasManyThrough(
            Report::class,
            MasterLesson::class,
            'user_id',
            'reportable_id'
        )->where('reportable_type', MasterLesson::class);
    }

    /**
     * Helpers
     */

    public function isReportedLessonsResolved(): bool
    {
        // check if user does not have a reported lesson that has not been resolved yet.
        return $this->reportedLessons()
            ->where('status', '!=', ReportStatus::RESOLVED)
            ->doesntExist();
    }
}
