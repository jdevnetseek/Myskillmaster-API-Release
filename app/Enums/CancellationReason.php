<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class CancellationReason extends Enum
{
    const WORK = 'work';
    const SICKNESS = 'sickness';
    const PERSONAL_REASONS = 'personal_reasons';
    const DATE_UNAVAILABILITY = 'date_unavailability';
    const OTHERS = 'others';
}
