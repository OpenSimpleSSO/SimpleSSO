<?php

namespace App\Entity;

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
     * @Mapping\Column(type="guid")
     */
    private $version;
    private $versionAlreadyChanged = false;

    /**
     * @var string
     *
     * @Mapping\Column(type="string", length=254)
     */
    private $emailAddress;

    /**
     * @var string
     *
     * @Mapping\Column(type="string",length=254, unique=true)
     */
    private $uniqueEmailAddress;

    /**
     * @var bool
     *
     * @Mapping\Column(type="boolean")
     */
    private $emailAddressVerified;

    /**
     * @var string
     *
     * @Mapping\Column(type="string", length=80)
     */
    private $firstName;

    /**
     * @var string
     *
     * @Mapping\Column(type="string", length=80)
     */
    private $lastName;

    /**
     * @var array
     *
     * @Mapping\Column(type="json", options={"jsonb": true})
     */
    private $roles;

    /**
     * @var string|null
     *
     * @Mapping\Column(type="string", length=60, nullable=true)
     */
    private $password;

    /**
     * @var bool
     *
     * @Mapping\Column(type="boolean")
     */
    private $enabled;

    /**
     * @var string|null
     *
     * @Mapping\Column(type="guid", nullable=true)
     */
    private $token;

    /**
     * @var \DateTimeInterface|null
     *
     * @Mapping\Column(type="datetime", nullable=true)
     */
    private $tokenExpirationDate;

    /**
     * @var array
     *
     * @Mapping\Column(type="json", options={"jsonb": true})
     */
    private $extraData;

    /**
     * UserAccount constructor.
     *
     * @param string $emailAddress
     * @param string $firstName
     * @param string $lastName
     * @throws \Exception
     */
    public function __construct(string $emailAddress, string $firstName, string $lastName)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->version = Uuid::uuid4()->toString();
        $this->versionAlreadyChanged = true;
        $this->emailAddress = $emailAddress;
        $this->uniqueEmailAddress = mb_strtolower($emailAddress);
        $this->emailAddressVerified = false;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->roles = [];
        $this->enabled = true;
        $this->extraData = [];
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
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    /**
     * @return bool
     */
    public function isEmailAddressVerified(): bool
    {
        return $this->emailAddressVerified;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * @return string[]
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getTokenExpirationDate(): ?\DateTimeInterface
    {
        return $this->tokenExpirationDate;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getAttribute(string $key)
    {
        if (!key_exists($key, $this->extraData)) {
            return null;
        }

        return $this->extraData[$key];
    }

    /**
     * @param string $emailAddress
     */
    public function setEmailAddress(string $emailAddress): void
    {
        if ($emailAddress !== $this->emailAddress) {
            $this->emailAddress = $emailAddress;
            $this->uniqueEmailAddress = mb_strtolower($emailAddress);
            $this->emailAddressVerified = false;
            $this->changeVersion();
        }
    }

    /**
     * @param bool $emailAddressVerified
     */
    public function setEmailAddressVerified(bool $emailAddressVerified): void
    {
        if ($emailAddressVerified !== $this->emailAddressVerified) {
            $this->emailAddressVerified = $emailAddressVerified;
            $this->changeVersion();
        }
    }

    /**
     * @param string $firstName
     * @param string $lastName
     */
    public function setName(string $firstName, string $lastName): void
    {
        if ($firstName !== $this->firstName) {
            $this->firstName = $firstName;
            $this->changeVersion();
        }
        if ($lastName !== $this->lastName) {
            $this->lastName = $lastName;
            $this->changeVersion();
        }
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): void
    {
        sort($roles);
        if (serialize($roles) !== serialize($this->roles)) {
            $this->roles = $roles;
            $this->changeVersion();
        }
    }

    /**
     * @param string|null $password
     */
    public function setPassword(?string $password): void
    {
        // Password does not change version.
        $this->password = $password;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        if ($enabled !== $this->enabled) {
            $this->enabled = $enabled;
            $this->changeVersion();
        }
    }

    /**
     *
     */
    public function resetToken(): void
    {
        // Token does not change version.
        $this->token = null;
        $this->tokenExpirationDate = null;
    }

    /**
     * @param \DateTimeInterface $expirationDate
     */
    public function generateToken(\DateTimeInterface $expirationDate): void
    {
        // Token does not change version.
        $this->token = Uuid::uuid4()->toString();
        $this->tokenExpirationDate = $expirationDate;
    }

    /**
     *
     */
    public function resetAttributes(): void
    {
        $this->extraData = [];
        $this->changeVersion();
    }

    /**
     * @param string $key
     * @param        $value
     */
    public function setAttribute(string $key, $value): void
    {
        if (!key_exists($key, $this->extraData) || $this->extraData[$key] !== $value) {
            $this->extraData[$key] = $value;
            $this->changeVersion();
        }
    }

    /**
     * Change the version of the entity.
     */
    private function changeVersion(): void
    {
        if (!$this->versionAlreadyChanged) {
            $this->version = Uuid::uuid4()->toString();
            $this->versionAlreadyChanged = true;
        }
    }

    // UserInterface methods.

    public function getSalt()           { return null; }
    public function getUsername()       { return $this->emailAddress; }
    public function eraseCredentials()  { }
}
