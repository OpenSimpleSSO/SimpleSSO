<?php

namespace App\Model;

use App\Entity\UserAccount;
use App\Model\Data\UserManagement\Registration;
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
     * @param Registration $data
     * @return UserAccount
     */
    public function create(Registration $data): UserAccount
    {
        $userAccount = new UserAccount($data->siren);
        $userAccount->setEmailAddress($data->emailAddress);
        $userAccount->emailAddressVerified = false;
        $userAccount->firstName = $data->firstName;
        $userAccount->lastName = $data->lastName;
        $userAccount->roles = [ 'ROLE_USER' ];
        $userAccount->password = $this->passwordEncoder->encodePassword($userAccount, $data->password);
        $userAccount->enabled = true;
        $this->repository->save($userAccount);

        return $userAccount;
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
}
