<?php

namespace App\Model\Data\Generic;

class BaseProfileEdition
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
     * @var array
     */
    public $extraData = [];
}
