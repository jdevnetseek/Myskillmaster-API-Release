<?php

namespace App\Lib\Oauth\Contracts;

use App\Lib\Oauth\User;

interface Provider
{

    /**
     * Get user from given access token
     *
     * @param string $idToken
     * @return User
     */
    public function userFromToken(string $accessToken): User;
}
