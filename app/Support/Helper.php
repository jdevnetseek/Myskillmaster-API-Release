<?php

namespace App\Support;

class Helper
{
    /**
     * Check if the given string is email
     *
     * @param string $var
     * @return boolean
     */
    public static function isEmail(string $var): bool
    {
        return !!filter_var($var, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Get class name from given namespace
     *
     * @param string $namespace
     * @param boolean $toLowerCase
     * @return string
     */
    public static function getClassName(string $namespace, $toLowerCase = false): string
    {
        $c = new \ReflectionClass($namespace);
        $name = $c->getShortName();

        return $toLowerCase ? strtolower($name) : $name;
    }
}
