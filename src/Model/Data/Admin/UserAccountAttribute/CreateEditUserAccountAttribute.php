<?php

namespace App\Model\Data\Admin\UserAccountAttribute;

use Symfony\Component\Validator\Constraints;

class CreateEditUserAccountAttribute
{
    /**
     * @var string
     *
     * @Constraints\NotBlank(message="admin.userAccountAttribute.createEditUserAccountAttribute.title.notBlank")
     * @Constraints\Length(
     *     min=2, minMessage="admin.userAccountAttribute.createEditUserAccountAttribute.title.minLength",
     *     max=80, maxMessage="admin.userAccountAttribute.createEditUserAccountAttribute.title.maxLength",
     * )
     */
    public $title;

    /**
     * @var string
     *
     * @Constraints\NotBlank(message="admin.userAccountAttribute.createEditUserAccountAttribute.key.notBlank")
     * @Constraints\Length(
     *     min=2, minMessage="admin.userAccountAttribute.createEditUserAccountAttribute.key.minLength",
     *     max=80, maxMessage="admin.userAccountAttribute.createEditUserAccountAttribute.key.maxLength",
     * )
     */
    public $key;

    /**
     * @var string
     *
     * @Constraints\NotBlank(message="admin.userAccountAttribute.createEditUserAccountAttribute.type.notBlank")
     * @Constraints\Choice(choices={"bool", "date", "datetime", "number", "text"}, message="admin.userAccountAttribute.createEditUserAccountAttribute.type.choice")
     */
    public $type;
}
