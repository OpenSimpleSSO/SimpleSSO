<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Mapping\Entity()
 * @Mapping\Table(indexes={
 *     @Mapping\Index(columns={"email_address"}),
 * })
 */
class UserAccount implements UserInterface
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
    private $organization;

    /**
     * @var string
     *
     * @Mapping\Column(type="string", length=254)
     */
    public $emailAddress;

    /**
     * @var string
     *
     * @Mapping\Column(type="string",length=254, unique=true)
     */
    public $uniqueEmailAddress;

    /**
     * @var bool
     *
     * @Mapping\Column(type="boolean")
     */
    public $emailAddressVerified;

    /**
     * @var string
     *
     * @Mapping\Column(type="string", length=80)
     */
    public $firstName;

    /**
     * @var string
     *
     * @Mapping\Column(type="string", length=80)
     */
    public $lastName;

    /**
     * @var array
     *
     * @Mapping\Column(type="json", options={"jsonb": true})
     */
    public $roles;

    /**
     * @var string|null
     *
     * @Mapping\Column(type="string", length=60, nullable=true)
     */
    public $password;

    /**
     * @var bool
     *
     * @Mapping\Column(type="boolean")
     */
    public $enabled;

    /**
     * @var string|null
     *
     * @Mapping\Column(type="guid", nullable=true)
     */
    public $token;

    /**
     * @var DateTime|null
     *
     * @Mapping\Column(type="datetime", nullable=true)
     */
    public $tokenExpirationDate;

    /**
     * UserAccount constructor.
     *
     * @param string $organization
     */
    public function __construct(string $organization)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->organization = $organization;
    }

    /**
     * @param string $organization
     */
    public function setOrganization(string $organization): void
    {
        $this->organization = $organization;
    }

    /**
     * Setup the email address.
     *
     * @param string $emailAddress
     */
    public function setEmailAddress(string $emailAddress): void
    {
        if ($emailAddress !== $this->emailAddress) {
            $this->emailAddress = $emailAddress;
            $this->uniqueEmailAddress = mb_strtolower($emailAddress);
            $this->emailAddressVerified = false;
        }
    }

    /**
     * Generate a new token that expires to the given date.
     *
     * @param DateTime $tokenExpirationDate
     */
    public function generateToken(DateTime $tokenExpirationDate)
    {
        $this->token = Uuid::uuid4()->toString();
        $this->tokenExpirationDate = $tokenExpirationDate;
    }

    /**
     * Erase the current token.
     */
    public function eraseToken()
    {
        $this->token = null;
        $this->tokenExpirationDate = null;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getOrganization(): string
    {
        return $this->organization;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    // UserInterface methods.

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return $this->emailAddress;
    }

    public function eraseCredentials()
    {

    }
}
