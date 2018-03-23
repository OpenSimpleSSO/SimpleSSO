<?php

namespace App\Model\Data\UserManagement;

use App\Model\Data\Generic\BaseProfileEdition;
use Symfony\Component\Validator\Constraints;

class ProfileEdition extends BaseProfileEdition
{
    /**
     * @var string
     *
     * @Constraints\NotBlank(message="userManagement.registration.firstName.notBlank")
     * @Constraints\Length(
     *     min=2, minMessage="userManagement.registration.firstName.minLength",
     *     max=80, maxMessage="userManagement.registration.firstName.maxLength",
     * )
     */
    public $firstName;

    /**
     * @var string
     *
     * @Constraints\NotBlank(message="userManagement.registration.lastName.notBlank")
     * @Constraints\Length(
     *     min=2, minMessage="userManagement.registration.lastName.minLength",
     *     max=80, maxMessage="userManagement.registration.lastName.maxLength",
     * )
     */
    public $lastName;

    /**
     * @var string
     *
     * @Constraints\NotBlank(message="userManagement.registration.emailAddress.notBlank")
     * @Constraints\Email(message="userManagement.registration.emailAddress.email")
     */
    public $emailAddress;

    /**
     * @var int
     *
     * @Constraints\NotNull(message="userManagement.registration.organization.notNull")
     * @Constraints\Length(
     *     min=2, minMessage="userManagement.registration.organization.minLength",
     *     max=80, maxMessage="userManagement.registration.organization.maxLength",
     * )
     */
    public $organization;
}
