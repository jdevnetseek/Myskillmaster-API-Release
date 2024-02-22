<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class UsernameType extends Enum
{
    /**
     * Using email as account username
     *
     * @var string
     */
    const EMAIL = 'email';

    /**
     * Using phone number as account username
     *
     * @var string
     */
    const PHONE_NUMBER = 'phone_number';
}
