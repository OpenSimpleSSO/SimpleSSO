<?php

namespace App\Model\Data\Api\User;

use App\Model\Data\Generic\BaseRegistration;
use Symfony\Component\Validator\Constraints;

class Registration extends BaseRegistration
{
    /**
     * @var array
     *
     * @Constraints\All({
     *     @Constraints\Length(min=6, minMessage="userAccount.roles.minLength")
     * })
     */
    public $roles;
}
