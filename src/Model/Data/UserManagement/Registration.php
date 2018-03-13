<?php

namespace App\Model\Data\UserManagement;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Registration
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
     * @Constraints\NotNull(message="userManagement.registration.siren.notNull")
     */
    public $siren;

    /**
     * @var string
     *
     * @Constraints\NotBlank(message="userManagement.registration.password.notBlank")
     * @Constraints\Length(
     *     min=10, minMessage="userManagement.registration.password.minLength",
     *     max=50, maxMessage="userManagement.registration.password.maxLength",
     * )
     */
    public $password;

    /**
     * @var string
     */
    public $passwordRepeat;

    /**
     * Validate password. Will check password strength and also that password repeat is OK. We do not use Symfony's
     * RepeatedType because we want password validation to occur even if passwords are different.
     *
     * @Constraints\Callback()
     *
     * @param ExecutionContextInterface $context
     */
    public function validatePassword(ExecutionContextInterface $context): void
    {
        // Check password strength.

        $containsLowerCase = false;
        $containsUpperCase = false;
        $containsDigit = false;
        for ($index = 0; $index < mb_strlen($this->password); ++$index) {
            $char = $this->password[$index];
            switch (true) {
                case $char >= 'a' && $char <= 'z':
                    $containsLowerCase = true;
                    break;

                case $char >= 'A' && $char <= 'Z':
                    $containsUpperCase = true;
                    break;

                case $char >= '0' && $char <= '9':
                    $containsDigit = true;
                    break;
            }
            if ($containsLowerCase && $containsUpperCase && $containsDigit) {
                break;
            }
        }
        if (!$containsLowerCase) {
            $context
                ->buildViolation('userManagement.registration.password.lowerCase')
                ->atPath('password')
                ->addViolation();
        }
        if (!$containsUpperCase) {
            $context
                ->buildViolation('userManagement.registration.password.upperCase')
                ->atPath('password')
                ->addViolation();
        }
        if (!$containsDigit) {
            $context
                ->buildViolation('userManagement.registration.password.digit')
                ->atPath('password')
                ->addViolation();
        }

        // Check repeat field.

        if ($this->password !== $this->passwordRepeat) {
            $context
                ->buildViolation('userManagement.registration.password.repeat')
                ->atPath('passwordRepeat')
                ->addViolation();
        }
    }
}
