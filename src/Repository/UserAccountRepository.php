<?php

namespace App\Repository;

use App\Entity\UserAccount;
use Vinorcola\HelperBundle\Repository;

class UserAccountRepository extends Repository
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass(): string
    {
        return UserAccount::class;
    }

    /**
     * @param UserAccount $userAccount
     */
    public function save(UserAccount $userAccount): void
    {
        $this->entityManager->persist($userAccount);
    }
}
