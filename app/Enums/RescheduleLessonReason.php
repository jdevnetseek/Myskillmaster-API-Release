<?php

namespace App\Enums;

use BenSampo\Enum\Contracts\LocalizedEnum;
use BenSampo\Enum\Enum;

final class RescheduleLessonReason extends Enum implements LocalizedEnum
{
    const WORK = 'work';
    const SICKNESS = 'sickness';
    const PERSONAL_REASONS = 'personal_reasons';
    const DATE_UNAVAILABILITY = 'date_unavailability';
    const OTHERS = 'others';
}
