<?php

namespace App\Model\Data\Api\User;

use App\Model\Data\Generic\BaseProfileEdition;
use Symfony\Component\Validator\Constraints;

class ProfileEdition extends BaseProfileEdition
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
