<?php

namespace App\Model;

use App\Entity\UserAccount;
use App\Entity\UserAccountAttribute;
use App\Model\Data\Admin\UserAccount\ProfileEdition as AdminProfileEdition;
use App\Model\Data\Api\User\ProfileEdition as ApiProfileEdition;
use App\Model\Data\Api\User\Registration as ApiRegistration;
use App\Model\Data\Generic\BasePasswordChange;
use App\Model\Data\Generic\BaseProfileEdition;
use App\Model\Data\Generic\BaseRegistration;
use App\Model\Data\UserProfile\ProfileEdition;
use App\Repository\UserAccountRepository;
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
     * @var UserAccountAttributeModel
     */
    private $attributeModel;

    /**
     * UserAccountModel constructor.
     *
     * @param UserAccountRepository        $repository
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param TokenStorageInterface        $tokenStorage
     * @param TokenModel                   $tokenModel
     * @param UserAccountAttributeModel    $attributeModel
     */
    public function __construct(
        UserAccountRepository $repository,
        UserPasswordEncoderInterface $passwordEncoder,
        TokenStorageInterface $tokenStorage,
        TokenModel $tokenModel,
        UserAccountAttributeModel $attributeModel
    ) {
        $this->repository = $repository;
        $this->passwordEncoder = $passwordEncoder;
        $this->tokenStorage = $tokenStorage;
        $this->tokenModel = $tokenModel;
        $this->attributeModel = $attributeModel;
    }

    /**
     * @param BaseRegistration $data
     * @return UserAccount
     */
    public function create(BaseRegistration $data): UserAccount
    {
        $userAccount = new UserAccount($data->emailAddress, $data->firstName, $data->lastName);
        $this->generateToken($userAccount);
        $userAccount->setRoles(
            $data instanceof ApiRegistration && $data->roles !== null ?
                $data->roles :
                [ 'ROLE_USER' ]
        );
        $userAccount->setPassword($this->passwordEncoder->encodePassword($userAccount, $data->password));
        $this->updateExtraData($userAccount, $data->extraData);
        $this->repository->save($userAccount);

        return $userAccount;
    }

    /**
     * @param UserAccount $userAccount
     * @return ProfileEdition
     */
    public function generateProfileEditionData(UserAccount $userAccount): ProfileEdition
    {
        $data = new ProfileEdition();
        $data->firstName = $userAccount->getFirstName();
        $data->lastName = $userAccount->getLastName();
        $data->emailAddress = $userAccount->getEmailAddress();
        foreach ($this->attributeModel->get() as $attribute) {
            $value = $userAccount->getAttribute($attribute->key);
            if ($value === null) {
                $data->extraData[$attribute->key] = null;
            } else {
                switch ($attribute->type) {
                    case UserAccountAttribute::TYPE_DATE:
                        $data->extraData[$attribute->key] = new \DateTimeImmutable($value);
                        break;

                    case UserAccountAttribute::TYPE_DATETIME:
                        $data->extraData[$attribute->key] = new \DateTimeImmutable($value);
                        break;

                    default:
                        $data->extraData[$attribute->key] = $value;
                }
            }
        }

        return $data;
    }

    /**
     * @param UserAccount $userAccount
     * @return AdminProfileEdition
     */
    public function generateAdminProfileEditionData(UserAccount $userAccount): AdminProfileEdition
    {
        $data = new AdminProfileEdition();
        $data->firstName = $userAccount->getFirstName();
        $data->lastName = $userAccount->getLastName();
        $data->emailAddress = $userAccount->getEmailAddress();
        foreach ($this->attributeModel->get() as $attribute) {
            $value = $userAccount->getAttribute($attribute->key);
            if ($value === null) {
                $data->extraData[$attribute->key] = null;
            } else {
                switch ($attribute->type) {
                    case UserAccountAttribute::TYPE_DATE:
                        $data->extraData[$attribute->key] = new \DateTimeImmutable($value);
                        break;

                    case UserAccountAttribute::TYPE_DATETIME:
                        $data->extraData[$attribute->key] = new \DateTimeImmutable($value);
                        break;

                    default:
                        $data->extraData[$attribute->key] = $value;
                }
            }
        }
        $data->roles = json_encode($userAccount->getRoles());
        $data->enabled = $userAccount->isEnabled();

        return $data;
    }

    /**
     * @param UserAccount        $userAccount
     * @param BaseProfileEdition $data
     */
    public function editProfile(UserAccount $userAccount, BaseProfileEdition $data): void
    {
        $this->updateEmailAddress($userAccount, $data->emailAddress);
        $userAccount->setName($data->firstName, $data->lastName);
        if ($data instanceof ApiProfileEdition) {
            $userAccount->setRoles($data->roles);
        }
        if ($data instanceof AdminProfileEdition) {
            $userAccount->setRoles(json_decode($data->roles));
            $userAccount->setEnabled($data->enabled);
        }
        $this->updateExtraData($userAccount, $data->extraData);
    }

    /**
     * @param UserAccount $userAccount
     * @param string      $password
     * @return bool
     */
    public function isPasswordValid(UserAccount $userAccount, string $password): bool
    {
        return $this->passwordEncoder->isPasswordValid($userAccount, $password);
    }

    /**
     * @param UserAccount        $userAccount
     * @param BasePasswordChange $data
     */
    public function changePassword(UserAccount $userAccount, BasePasswordChange $data): void
    {
        $userAccount->setPassword($this->passwordEncoder->encodePassword($userAccount, $data->password));
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
        $userAccount->setEnabled(true);
    }

    /**
     * @param UserAccount $userAccount
     */
    public function disable(UserAccount $userAccount): void
    {
        $userAccount->setEnabled(false);
    }

    /**
     * Generate a new token that expires at the given date.
     *
     * @param UserAccount $userAccount
     */
    public function generateToken(UserAccount $userAccount)
    {
        $userAccount->generateToken(
            $this->tokenModel->getExpirationDate(self::TOKEN_VALIDITY_INTERVAL)
        );
    }

    /**
     * @param UserAccount $userAccount
     */
    public function verifyEmailAddress(UserAccount $userAccount): void
    {
        $userAccount->setEmailAddressVerified(true);
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
        if ($emailAddress !== $userAccount->getEmailAddress()) {
            $userAccount->setEmailAddress($emailAddress);
            $this->generateToken($userAccount);
        }
    }

    /**
     * Erase the current token.
     *
     * @param UserAccount $userAccount
     */
    private function eraseToken(UserAccount $userAccount)
    {
        $userAccount->resetToken();
    }

    /**
     * @param UserAccount $userAccount
     * @param array       $extraData
     */
    private function updateExtraData(UserAccount $userAccount, array $extraData)
    {
        $userAccount->resetAttributes();
        foreach ($this->attributeModel->get() as $attribute) {
            $value = $extraData[$attribute->key] ?? null;
            if ($value === null) {
                $userAccount->setAttribute($attribute->key, null);
            } else {
                switch ($attribute->type) {
                    case UserAccountAttribute::TYPE_DATE:
                        $userAccount->setAttribute(
                            $attribute->key,
                            $value instanceof \DateTimeInterface ?
                                $value->format('Y-m-d') :
                                $value
                        );
                        break;

                    case UserAccountAttribute::TYPE_DATETIME:
                        $userAccount->setAttribute(
                            $attribute->key,
                            $value instanceof \DateTimeInterface ?
                                $value->format(DATE_ATOM) :
                                $value
                        );
                        break;

                    default:
                        $userAccount->setAttribute($attribute->key, $value);
                }
            }
        }
    }
}
