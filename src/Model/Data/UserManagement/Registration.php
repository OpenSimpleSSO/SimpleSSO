<?php

namespace App\Model\Data\UserManagement;

use App\Model\Data\Generic\BaseRegistration;
use App\Model\Data\Generic\PasswordRepeatTrait;

class Registration extends BaseRegistration
{
    use PasswordRepeatTrait;
}
