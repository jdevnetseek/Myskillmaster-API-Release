<?php

namespace App\Support;

class CodeGenerator
{
    /**
     * Generate a n digit of numiric string.
     *
     * @param integer $digits
     * @return string
     */
    public static function make($digits = 5) : string
    {
        return str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
    }
}
