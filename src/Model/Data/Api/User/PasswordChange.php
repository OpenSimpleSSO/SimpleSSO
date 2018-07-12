<?php

namespace App\Model\Data\Api\User;

use App\Model\Data\Generic\BasePasswordChange;
use Symfony\Component\Validator\Constraints;

class PasswordChange extends BasePasswordChange
{
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
}
