<?php

namespace App\Model\Data\PasswordRecovery;

use Symfony\Component\Validator\Constraints;

class RequirePasswordRecovery
{
    /**
     * @var string
     *
     * @Constraints\NotBlank(message="userAccount.emailAddress.notBlank")
     * @Constraints\Email(message="userAccount.emailAddress.email")
     */
    public $emailAddress;
}
