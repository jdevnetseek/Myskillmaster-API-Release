<?php

namespace App\Support;

class Boolean
{
    /**
     * Cast the given variable to boolean
     *
     * @param Any $var
     * @return boolean
     */
    public static function cast($var) : bool
    {
        if (is_string($var)) {
            if (strtolower($var) == 'false') {
                return false;
            }
        }
        return boolval($var);
    }
}
