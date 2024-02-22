<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class Role extends Enum
{
    /**
     * This roles has no restriction
     */
    const SUPER_ADMIN = 'SUPER_ADMIN';

    const ADMIN = 'ADMIN';

    const USER = 'USER';

    /**
     * The default role for all new added user
     *
     * @return string
     */
    public static function default(): string
    {
        return static::USER;
    }

    /**
     * Set default permission for each role
     *
     * @return array
     */
    public static function defaultPermissions() : array
    {
        return [
            // Adding "ALL" will give all permission
            static::ADMIN => ['ALL'],

            static::USER => [
                Permission::VIEW_USERS,
            ]
        ];
    }

    /**
     * Get permissions to a given role
     *
     * @param string $role
     * @return array
     */
    public static function getPermissions($role) : array
    {
        if (isset(static::defaultPermissions()[$role])) {
            return static::defaultPermissions()[$role];
        } else {
            return [];
        }
    }
}
