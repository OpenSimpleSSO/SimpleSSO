<?php

namespace App\Model\Data\Generic;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait PasswordTrait
{
    /**
     * @var string
     *
     * @Constraints\NotBlank(message="userAccount.password.notBlank")
     * @Constraints\Length(
     *     min=10, minMessage="userAccount.password.minLength",
     *     max=50, maxMessage="userAccount.password.maxLength",
     * )
     */
    public $password;

    /**
     * Validate password strength.
     *
     * @Constraints\Callback()
     *
     * @param ExecutionContextInterface $context
     */
    public function validatePasswordStrength(ExecutionContextInterface $context): void
    {
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
                ->buildViolation('userAccount.password.lowerCase')
                ->atPath('password')
                ->addViolation();
        }
        if (!$containsUpperCase) {
            $context
                ->buildViolation('userAccount.password.upperCase')
                ->atPath('password')
                ->addViolation();
        }
        if (!$containsDigit) {
            $context
                ->buildViolation('userAccount.password.digit')
                ->atPath('password')
                ->addViolation();
        }
    }
}
