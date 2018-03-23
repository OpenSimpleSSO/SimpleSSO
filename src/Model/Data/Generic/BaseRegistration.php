<?php

namespace App\Model\Data\Generic;

class BaseRegistration
{
    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var string
     */
    public $emailAddress;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $passwordRepeat;

    /**
     * @var array
     */
    public $extraData = [];
}
