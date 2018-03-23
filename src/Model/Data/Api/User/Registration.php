<?php

namespace App\Model\Data\Api\User;

use App\Model\Data\Generic\BaseRegistration;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Registration extends BaseRegistration
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
     * @var string
     *
     * @Constraints\NotBlank(message="Must provide a password.")
     * @Constraints\Length(
     *     min=10, minMessage="Password must be at least {{ limit }} characters long.",
     *     max=50, maxMessage="Password cannot exceed {{ limit }} characters.",
     * )
     */
    public $password;

    /**
     * Validate password. Will check password strength.
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
                ->buildViolation('Password must contains at least one lower case letter.')
                ->atPath('password')
                ->addViolation();
        }
        if (!$containsUpperCase) {
            $context
                ->buildViolation('Password must contains at least one upper case letter.')
                ->atPath('password')
                ->addViolation();
        }
        if (!$containsDigit) {
            $context
                ->buildViolation('Password must contains at least one digit.')
                ->atPath('password')
                ->addViolation();
        }
    }
}
