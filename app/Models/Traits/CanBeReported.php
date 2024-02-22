<?php

namespace App\Models\Traits;

use App\Models\User;
use App\Models\Report;
use App\Enums\MediaCollectionType;
use App\Enums\ReportStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

trait CanBeReported
{
    /**
     * The list of reports that was filed against the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function reports()
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    /**
     * File a report as the authenticated user.
     *
     * @param array $reasonIds
     * @param string $description
     * @param array $attachments
     * @return Report
     */
    public function report(array $reasonIds, string $description = null, array $attachments = []): Report
    {
        return $this->reportAsUser(auth()->user(), $reasonIds, $description, $attachments);
    }

    /**
     * File a report as the specified user.
     *
     * @param array $reasonIds
     * @param string $description
     * @param array $attachments
     * @return Report
     */
    public function reportAsUser(User $user, array $reasonIds, string $description = null, array $attachments = []): Report
    {
        return DB::transaction(function () use ($user, $reasonIds, $description, $attachments) {
            $report = new Report();
            $report->description  = $description;
            $report->status  = ReportStatus::PENDING;
            $report->reported_by  = $user->getKey();

            $this->reports()->save($report);

            $report->reasons()->sync($reasonIds);

            foreach ($attachments as $attachment) {
                $report->addMedia($attachment)->toMediaCollection(MediaCollectionType::REPORT_ATTACHMENTS);
            }

            return $report;
        });
    }

    /**
     * Check if is reported by current authenticated user.
     *
     * @return boolean
     */
    public function isReported()
    {
        return $this->isReportedAsUser(auth()->user());
    }

    /**
     * Check if reported by user.
     *
     * @param User $user
     * @return boolean
     */
    public function isReportedAsUser(User $user)
    {
        return $this->reports()->whereReportedBy($user->getKey())->exists();
    }

    /**
     * Filter reported by current user.
     *
     * @param Builder $query
     * @return void
     */
    public function scopeAppendIsReported(Builder $query)
    {
        $query->appendIsReportedAsUser(auth()->user());
    }

    /**
     * Filter reported by specified user.
     *
     * @param Builder $query
     * @param User $user
     * @return void
     */
    public function scopeAppendIsReportedAsUser(Builder $query, User $user)
    {
        $query->addSelect([
            'is_reported' => Report::query()
                ->selectRaw('count(id) as is_reported')
                ->where((new Report)->qualifyColumn('reportable_type'), $this->getMorphClass())
                ->whereColumn((new Report)->qualifyColumn('reportable_id'), $this->qualifyColumn('id'))
                ->take(1)
        ]);

        $query->withCasts([
            'is_reported' => 'boolean'
        ]);
    }
}
