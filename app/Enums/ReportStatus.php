<?php

namespace App\Enums;

use BenSampo\Enum\Enum;


final class ReportStatus extends Enum
{
    const PENDING  = 'PENDING';
    const BLOCKED  =   'BLOCKED';
    const IGNORED  = 'IGNORED';
    const RESOLVED = 'RESOLVED';
}
