<?php

namespace App\Model\Data\Generic;

use Symfony\Component\Validator\Constraints;

class BaseProfileEdition
{
    /**
     * @var string
     *
     * @Constraints\NotBlank(message="userAccount.firstName.notBlank")
     * @Constraints\Length(
     *     min=2, minMessage="userAccount.firstName.minLength",
     *     max=80, maxMessage="userAccount.firstName.maxLength",
     * )
     */
    public $firstName;

    /**
     * @var string
     *
     * @Constraints\NotBlank(message="userAccount.lastName.notBlank")
     * @Constraints\Length(
     *     min=2, minMessage="userAccount.lastName.minLength",
     *     max=80, maxMessage="userAccount.lastName.maxLength",
     * )
     */
    public $lastName;

    /**
     * @var string
     *
     * @Constraints\NotBlank(message="userAccount.emailAddress.notBlank")
     * @Constraints\Email(message="userAccount.emailAddress.email")
     */
    public $emailAddress;

    /**
     * @var array
     */
    public $extraData = [];
}
