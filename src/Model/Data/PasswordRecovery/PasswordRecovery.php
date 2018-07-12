<?php

namespace App\Model\Data\PasswordRecovery;

use App\Model\Data\Generic\BasePasswordChange;
use App\Model\Data\Generic\PasswordRepeatTrait;

class PasswordRecovery extends BasePasswordChange
{
    use PasswordRepeatTrait;
}
