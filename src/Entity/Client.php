<?php

namespace App\Entity;

use Doctrine\ORM\Mapping;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Mapping\Entity()
 */
class Client implements UserInterface
{
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
     * @Mapping\Column(type="text")
     */
    public $publicKey;

    /**
     * @var string
     *
     * @Mapping\Column(type="string")
     */
    public $redirectUrl;

    /**
     * @var string|null
     */
    public $currentTokenData;

    /**
     * Client constructor.
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

    // UserInterface methods.

    public function getRoles()
    {
        return [
            'ROLE_CLIENT',
        ];
    }

    public function getPassword()
    {
        return null;
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return $this->title;
    }

    public function eraseCredentials()
    {

    }
}
