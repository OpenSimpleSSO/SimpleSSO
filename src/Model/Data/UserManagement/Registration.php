<?php

namespace App\Model\Data\UserManagement;

use App\Model\Data\Generic\BaseRegistration;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Registration extends BaseRegistration
{
    /**
     * @var string
     */
    public $passwordRepeat;

    /**
     * Validate password repeat. We do not use Symfony's RepeatedType because we want password validation to occur even
     * if passwords are different.
     *
     * @Constraints\Callback()
     *
     * @param ExecutionContextInterface $context
     */
    public function validatePasswordRepeat(ExecutionContextInterface $context): void
    {
        if ($this->password !== $this->passwordRepeat) {
            $context
                ->buildViolation('userAccount.password.repeat')
                ->atPath('passwordRepeat')
                ->addViolation();
        }
    }
}
