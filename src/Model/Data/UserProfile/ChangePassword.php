<?php

namespace App\Model\Data\UserProfile;

use App\Model\Data\Generic\BasePasswordChange;
use App\Model\Data\Generic\PasswordRepeatTrait;
use Symfony\Component\Validator\Constraints;

class ChangePassword extends BasePasswordChange
{
    use PasswordRepeatTrait;

    /**
     * @var string
     *
     * @Constraints\NotBlank(message="userAccount.password.notBlank")
     */
    public $currentPassword;
}
