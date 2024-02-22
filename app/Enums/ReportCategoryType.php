<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class ReportCategoryType extends Enum
{
    const USER     = 'users';
    const POST     = 'posts';
    const PRODUCT  = 'products';
    const COMMENT  = 'comments';
    const JOB      = 'jobs';
    const LESSONS  = 'lessons';
}
