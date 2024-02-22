<?php

namespace App\Actions;

use Exception;
use RuntimeException;
use Illuminate\Support\Arr;
use App\Models\Traits\CanBeReported;
use App\Models\Traits\InteractsWithReportableTypes;

class SubmitReport
{
    use InteractsWithReportableTypes;

    /**
     * Excute the action to submit the report.
     *
     * @param string $type
     * @param mixed $reportId
     * @param integer $reasonId
     * @param string $description
     * @param array $attachments
     * @return void
     */
    public function execute(string $type, $reportId, int $reasonId, string $description = null, array $attachments = [])
    {
        throw_unless(
            $this->hasReportableType($type),
            RuntimeException::class,
            "The type {$type} is not valid."
        );

        $class = $this->getReportableType($type);

        $trait = CanBeReported::class;

        throw_unless(
            Arr::has(class_uses_recursive($class), CanBeReported::class),
            RuntimeException::class,
            "The ${class} does not implement the required trait: ${trait}"
        );

        $instance = new $class;

        /** @var CanBeReported */
        $model = $instance->query()->findOrFail($reportId);

        return $model->report($reasonId, $description, $attachments);
    }
}
