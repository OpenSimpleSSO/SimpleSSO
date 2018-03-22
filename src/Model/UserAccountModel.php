<?php

namespace App\Model;

use App\Entity\UserAccount;
use App\Model\Data\Api\User\PasswordChange;
use App\Model\Data\Api\User\ProfileEdition;
use App\Model\Data\Generic\BaseRegistration;
use App\Repository\UserAccountRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserAccountModel
{
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
     * UserAccountModel constructor.
     *
     * @param UserAccountRepository        $repository
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param TokenStorageInterface        $tokenStorage
     */
    public function __construct(
        UserAccountRepository $repository,
        UserPasswordEncoderInterface $passwordEncoder,
        TokenStorageInterface $tokenStorage
    ) {
        $this->repository = $repository;
        $this->passwordEncoder = $passwordEncoder;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param BaseRegistration $data
     * @return UserAccount
     */
    public function create(BaseRegistration $data): UserAccount
    {
        $userAccount = new UserAccount($data->organization);
        $userAccount->setEmailAddress($data->emailAddress);
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
        $userAccount->setOrganization($data->organization);
        $userAccount->setEmailAddress($data->emailAddress);
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
            new UsernamePasswordToken($userAccount, null, 'main', $userAccount->getRoles())
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
}
