<?php

namespace App\Model;

use App\Entity\UserAccount;
use App\Entity\UserAccountAttribute;
use App\Model\Data\Api\User\PasswordChange;
use App\Model\Data\Api\User\ProfileEdition as ApiProfileEdition;
use App\Model\Data\Generic\BaseProfileEdition;
use App\Model\Data\Generic\BaseRegistration;
use App\Model\Data\UserManagement\ProfileEdition;
use App\Repository\UserAccountRepository;
use DateTime;
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
        $userAccount = new UserAccount($data->emailAddress);
        $this->generateToken($userAccount);
        $userAccount->firstName = $data->firstName;
        $userAccount->lastName = $data->lastName;
        $userAccount->roles = [ 'ROLE_USER' ];
        $userAccount->password = $this->passwordEncoder->encodePassword($userAccount, $data->password);
        $userAccount->enabled = true;
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
        $data->firstName = $userAccount->firstName;
        $data->lastName = $userAccount->lastName;
        $data->emailAddress = $userAccount->emailAddress;
        foreach ($this->attributeModel->get() as $attribute) {
            $value = $userAccount->getAttribute($attribute->key);
            if ($value === null) {
                $data->extraData[$attribute->key] = null;
            } else {
                switch ($attribute->type) {
                    case UserAccountAttribute::TYPE_DATE:
                        $data->extraData[$attribute->key] = new DateTime($value);
                        break;

                    case UserAccountAttribute::TYPE_DATETIME:
                        $data->extraData[$attribute->key] = new DateTime($value);
                        break;

                    default:
                        $data->extraData[$attribute->key] = $value;
                }
            }
        }

        return $data;
    }

    /**
     * @param UserAccount        $userAccount
     * @param BaseProfileEdition $data
     */
    public function editProfile(UserAccount $userAccount, BaseProfileEdition $data): void
    {
        $this->updateEmailAddress($userAccount, $data->emailAddress);
        $userAccount->firstName = $data->firstName;
        $userAccount->lastName = $data->lastName;
        if ($data instanceof ApiProfileEdition) {
            $userAccount->roles = $data->roles;
        }
        $this->updateExtraData($userAccount, $data->extraData);
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
     * Generate a new token that expires at the given date.
     *
     * @param UserAccount $userAccount
     */
    public function generateToken(UserAccount $userAccount)
    {
        $userAccount->token = Uuid::uuid4()->toString();
        $userAccount->tokenExpirationDate = $this->tokenModel->getExpirationDate(self::TOKEN_VALIDITY_INTERVAL);
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
     * Erase the current token.
     *
     * @param UserAccount $userAccount
     */
    private function eraseToken(UserAccount $userAccount)
    {
        $userAccount->token = null;
        $userAccount->tokenExpirationDate = null;
    }

    /**
     * @param UserAccount $userAccount
     * @param array       $extraData
     */
    private function updateExtraData(UserAccount $userAccount, array $extraData)
    {
        $userAccount->extraData = [];
        foreach ($this->attributeModel->get() as $attribute) {
            $value = $extraData[$attribute->key] ?? null;
            if ($value === null) {
                $userAccount->extraData[$attribute->key] = null;
            } else {
                switch ($attribute->type) {
                    case UserAccountAttribute::TYPE_DATE:
                        $userAccount->extraData[$attribute->key] = $value instanceof DateTime ?
                            $value->format('Y-m-d') :
                            $value;
                        break;

                    case UserAccountAttribute::TYPE_DATETIME:
                        $userAccount->extraData[$attribute->key] = $value instanceof DateTime ?
                            $value->format(DATE_ATOM) :
                            $value;
                        break;

                    default:
                        $userAccount->extraData[$attribute->key] = $value;
                }
            }
        }
    }
}
