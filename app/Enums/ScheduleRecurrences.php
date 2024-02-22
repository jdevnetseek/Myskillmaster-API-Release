<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class ScheduleRecurrences extends Enum
{
    const WEEK = 'week';
    const MONTH = 'month';
    const CUSTOM = 'custom';
}
