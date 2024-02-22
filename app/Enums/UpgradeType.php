<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class UpgradeType extends Enum
{
    const REQUIRED    = 'required';
    const RECOMMENDED = 'recommended';
    const DEFAULT     = 'default';
}
