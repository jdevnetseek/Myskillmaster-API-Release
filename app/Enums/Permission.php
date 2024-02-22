<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class Permission extends Enum
{

    /*
    |--------------------------------------------------------------------------
    | Roles Permission
    |--------------------------------------------------------------------------
    */

    /**
     * Permission to assign role to a user
     */
    const ASSIGN_ROLE = 'ASSIGN_ROLE';

    /**
     * Permission to add new role
     */
    const ADD_ROLE = 'ADD_ROLE';

    /**
     * Permission to delete a role
     */
    const DELETE_ROLE = 'DELETE_ROLE';

    /*
    |--------------------------------------------------------------------------
    | Users Permission
    |--------------------------------------------------------------------------
    */

    /**
     * Permission to view user list
     */
    const VIEW_USERS = 'VIEW_USERS';

    /**
     * Permission to update other user
     */
    const UPDATE_USERS = 'UPDATE_USERS';

    /**
     * Permission to delete other user
     */
    const DELETE_USERS = 'DELETE_USERS';
}
