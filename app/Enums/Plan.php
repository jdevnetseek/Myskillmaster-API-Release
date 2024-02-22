<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class Plan extends Enum
{
    const BASIC_PLAN  = 'basic-plan';
    const PRO_PLAN    = 'pro-plan';
    const MASTER_PLAN = 'master-plan';
    const MAESTRO_PLAN = 'maestro-plan';
}
