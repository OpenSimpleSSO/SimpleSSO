<?php

namespace App\Entity;

use Doctrine\ORM\Mapping;
use Ramsey\Uuid\Uuid;

/**
 * @Mapping\Entity()
 */
class UserAccountAttribute
{
    public const TYPE_BOOL = 'bool';
    public const TYPE_DATE = 'date';
    public const TYPE_DATETIME = 'datetime';
    public const TYPE_NUMBER = 'number';
    public const TYPE_TEXT = 'text';

    /**
     * @var string
     *
     * @Mapping\Column(type="guid")
     * @Mapping\Id()
     */
    private $id;

    /**
     * @var string
     *
     * @Mapping\Column(type="string", length=80)
     */
    public $title;

    /**
     * @var string
     *
     * @Mapping\Column(type="string", length=80, unique=true)
     */
    public $key;

    /**
     * @var string
     *
     * @Mapping\Column(type="string", length=8)
     */
    public $type;

    /**
     * UserAccountAttribute constructor.
     */
    public function __construct()
    {
        $this->id = Uuid::uuid4()->toString();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}
