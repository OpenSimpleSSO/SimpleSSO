<?php

namespace App\Model;

use App\Entity\UserAccount;
use App\Model\Data\Api\User\PasswordChange;
use App\Model\Data\Api\User\ProfileEdition;
use App\Model\Data\Generic\BaseRegistration;
use App\Repository\UserAccountRepository;
use Ramsey\Uuid\Uuid;
use SimpleSSO\CommonBundle\Model\TokenModel;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserAccountModel
{
    private const FIREWALL_KEY = 'main';
    private const TOKEN_VALIDITY_INTERVAL = 'PT15M';

    /**
     * @var UserAccountRepository
     */
    private $repository;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var TokenModel
     */
    private $tokenModel;

    /**
     * UserAccountModel constructor.
     *
     * @param UserAccountRepository        $repository
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param TokenStorageInterface        $tokenStorage
     * @param TokenModel                   $tokenModel
     */
    public function __construct(
        UserAccountRepository $repository,
        UserPasswordEncoderInterface $passwordEncoder,
        TokenStorageInterface $tokenStorage,
        TokenModel $tokenModel
    ) {
        $this->repository = $repository;
        $this->passwordEncoder = $passwordEncoder;
        $this->tokenStorage = $tokenStorage;
        $this->tokenModel = $tokenModel;
    }

    /**
     * @param BaseRegistration $data
     * @return UserAccount
     */
    public function create(BaseRegistration $data): UserAccount
    {
        $userAccount = new UserAccount($data->emailAddress);
        $this->generateToken($userAccount);
        $userAccount->organization = $data->organization;
        $userAccount->firstName = $data->firstName;
        $userAccount->lastName = $data->lastName;
        $userAccount->roles = [ 'ROLE_USER' ];
        $userAccount->password = $this->passwordEncoder->encodePassword($userAccount, $data->password);
        $userAccount->enabled = true;
        $this->repository->save($userAccount);

        return $userAccount;
    }

    /**
     * @param UserAccount    $userAccount
     * @param ProfileEdition $data
     */
    public function editProfile(UserAccount $userAccount, ProfileEdition $data): void
    {
        $this->updateEmailAddress($userAccount, $data->emailAddress);
        $userAccount->organization = $data->organization;
        $userAccount->firstName = $data->firstName;
        $userAccount->lastName = $data->lastName;
        $userAccount->roles = $data->roles;
    }

    /**
     * @param UserAccount    $userAccount
     * @param PasswordChange $data
     */
    public function changePassword(UserAccount $userAccount, PasswordChange $data): void
    {
        $userAccount->password = $this->passwordEncoder->encodePassword($userAccount, $data->password);
    }

    /**
     * @param UserAccount $userAccount
     */
    public function forceAuthentication(UserAccount $userAccount): void
    {
        $this->tokenStorage->setToken(
            new UsernamePasswordToken($userAccount, null, self::FIREWALL_KEY, $userAccount->getRoles())
        );
    }

    /**
     * @param UserAccount $userAccount
     */
    public function enable(UserAccount $userAccount): void
    {
        $userAccount->enabled = true;
    }

    /**
     * @param UserAccount $userAccount
     */
    public function disable(UserAccount $userAccount): void
    {
        $userAccount->enabled = false;
    }

    /**
     * @param UserAccount $userAccount
     */
    public function verifyEmailAddress(UserAccount $userAccount): void
    {
        $userAccount->emailAddressVerified = true;
        $this->eraseToken($userAccount);
    }

    /**
     * Update the user account's email address.
     *
     * @param UserAccount $userAccount
     * @param string      $emailAddress
     */
    private function updateEmailAddress(UserAccount $userAccount, string $emailAddress): void
    {
        if ($emailAddress !== $userAccount->emailAddress) {
            $userAccount->emailAddress = $emailAddress;
            $userAccount->uniqueEmailAddress = mb_strtolower($emailAddress);
            $userAccount->emailAddressVerified = false;
            $this->generateToken($userAccount);
        }
    }

    /**
     * Generate a new token that expires at the given date.
     *
     * @param UserAccount $userAccount
     */
    private function generateToken(UserAccount $userAccount)
    {
        $userAccount->token = Uuid::uuid4()->toString();
        $userAccount->tokenExpirationDate = $this->tokenModel->getExpirationDate(self::TOKEN_VALIDITY_INTERVAL);
    }

    /**
     * Erase the current token.
     *
     * @param UserAccount $userAccount
     */
    private function eraseToken(UserAccount $userAccount)
    {
        $userAccount->token = null;
        $userAccount->tokenExpirationDate = null;
    }
}
