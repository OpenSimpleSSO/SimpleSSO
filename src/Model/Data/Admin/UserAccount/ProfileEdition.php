<?php

namespace App\Model\Data\Admin\UserAccount;

use App\Model\Data\Generic\BaseProfileEdition;
use App\Validation\JsonConstraint;
use Symfony\Component\Validator\Constraints;

class ProfileEdition extends BaseProfileEdition
{
    /**
     * @var string
     *
     * @JsonConstraint()
     * @Constraints\NotNull()
     */
    public $roles;

    /**
     * @var bool
     */
    public $enabled;
}
