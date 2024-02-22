<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class AddressType extends Enum
{
    const HOME     = 'home';
    const WORK     = 'work';
    const DELIVERY = 'delivery';
    const BILLING  = 'billing';
    const LESSON   = 'lesson';
}
