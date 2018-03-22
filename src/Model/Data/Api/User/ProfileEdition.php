<?php

namespace App\Model\Data\Api\User;

use Symfony\Component\Validator\Constraints;

class ProfileEdition
{
    /**
     * @var string
     *
     * @Constraints\NotBlank(message="Must provide a first name.")
     * @Constraints\Length(
     *     min=2, minMessage="First name must be at least {{ limit }} characters long.",
     *     max=80, maxMessage="First name cannot exceed {{ limit }} characters.",
     * )
     */
    public $firstName;

    /**
     * @var string
     *
     * @Constraints\NotBlank(message="Must provide a last name.")
     * @Constraints\Length(
     *     min=2, minMessage="Last name must be at least {{ limit }} characters long.",
     *     max=80, maxMessage="Last name cannot exceed {{ limit }} characters.",
     * )
     */
    public $lastName;

    /**
     * @var string
     *
     * @Constraints\NotBlank(message="Must provide an email address.")
     * @Constraints\Email(message="Email address must be valid.")
     */
    public $emailAddress;

    /**
     * @var int
     *
     * @Constraints\NotNull(message="Must provide an organization's name.")
     * @Constraints\Length(
     *     min=2, minMessage="Organization's name must be at least {{ limit }} characters long.",
     *     max=80, maxMessage="Organization's name cannot exceed {{ limit }} characters.",
     * )
     */
    public $organization;

    /**
     * @var array
     *
     * @Constraints\All({
     *     @Constraints\Length(min=6, minMessage="Role must be at least {{ limit }} characters long.")
     * })
     */
    public $roles;
}