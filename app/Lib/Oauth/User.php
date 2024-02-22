<?php

namespace App\Lib\Oauth;

class User
{
    /**
     * The unique identifier for the user.
     *
     * @var mixed
     */
    public $id;

    /**
     * The user's full name.
     *
     * @var string
     */
    public $name;

    /**
     * The user's first name.
     *
     * @var string
     */
    public $firstName;

    /**
     * The user's last name.
     *
     * @var string
     */
    public $lastName;

    /**
     * The user's e-mail address.
     *
     * @var string
     */
    public $email;

    /**
     * The user's avatar image URL.
     *
     * @var string
     */
    public $avatar;

    /**
     * Get the unique identifier for the user.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the full name of the user.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the first name of the user.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Get the last name of the user.
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Get the e-mail address of the user.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get the avatar / image URL for the user.
     *
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }
}
