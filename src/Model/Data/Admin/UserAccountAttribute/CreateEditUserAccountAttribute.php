<?php

namespace App\Model\Data\Admin\UserAccountAttribute;

use Symfony\Component\Validator\Constraints;

class CreateEditUserAccountAttribute
{
    /**
     * @var string
     *
     * @Constraints\NotBlank(message="userAccountAttribute.title.notBlank")
     * @Constraints\Length(
     *     min=2, minMessage="userAccountAttribute.title.minLength",
     *     max=80, maxMessage="userAccountAttribute.title.maxLength",
     * )
     */
    public $title;

    /**
     * @var string
     *
     * @Constraints\NotBlank(message="userAccountAttribute.key.notBlank")
     * @Constraints\Length(
     *     min=2, minMessage="userAccountAttribute.key.minLength",
     *     max=80, maxMessage="userAccountAttribute.key.maxLength",
     * )
     * @Constraints\NotIdenticalTo(value="id", message="userAccountAttribute.key.alreadyUsed")
     * @Constraints\NotIdenticalTo(value="emailAddress", message="userAccountAttribute.key.alreadyUsed")
     * @Constraints\NotIdenticalTo(value="emailAddressVerified", message="userAccountAttribute.key.alreadyUsed")
     * @Constraints\NotIdenticalTo(value="firstName", message="userAccountAttribute.key.alreadyUsed")
     * @Constraints\NotIdenticalTo(value="lastName", message="userAccountAttribute.key.alreadyUsed")
     * @Constraints\NotIdenticalTo(value="roles", message="userAccountAttribute.key.alreadyUsed")
     * @Constraints\NotIdenticalTo(value="enabled", message="userAccountAttribute.key.alreadyUsed")
     */
    public $key;

    /**
     * @var string
     *
     * @Constraints\NotBlank(message="userAccountAttribute.type.notBlank")
     * @Constraints\Choice(choices={"bool", "date", "datetime", "number", "text"}, message="userAccountAttribute.type.choice")
     */
    public $type;
}
