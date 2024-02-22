<?php

namespace App\Models\Traits;

use Illuminate\Support\Arr;

trait InteractsWithReportableTypes
{
    /**
     * By default laravel uses namespace names whens storing
     * a morph type when this was not set via Relation::morphMap.
     * The purpose of this is to simplify http request parameters so that clients
     * will be able to pass the simplified names instead of the long namespaced class names.
     */

    /**
     * Return the list of mapped report types.
     *
     * @return array
     */
    public function getReportableTypes() : array
    {
        return [
            \App\Enums\ReportCategoryType::COMMENT => \App\Models\Comment::class,
            \App\Enums\ReportCategoryType::JOB     => \App\Models\Job::class,
            \App\Enums\ReportCategoryType::POST    => \App\Models\Post::class,
            \App\Enums\ReportCategoryType::PRODUCT => \App\Models\Product::class,
            \App\Enums\ReportCategoryType::USER    => \App\Models\User::class,
            \App\Enums\ReportCategoryType::LESSONS => \App\Models\MasterLesson::class,
        ];
    }

    /**
     * Checks if the type was mapped to its model.
     *
     * @param string $type
     * @return boolean
     */
    public function hasReportableType(string $type) : bool
    {
        return Arr::has($this->getReportableTypes(), $type);
    }

    /**
     * Returns the class of mapped report type.
     *
     * @param string $type
     * @return string
     */
    public function getReportableType(string $type) : string
    {
        return Arr::get($this->getReportableTypes(), $type);
    }
}
