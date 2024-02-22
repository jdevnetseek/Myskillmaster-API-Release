<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class Platform extends Enum
{
    const IOS     =  'ios';
    const ANDROID =  'android';
}
