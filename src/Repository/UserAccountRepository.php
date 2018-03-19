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

    /**
     * @param string $userAccountId
     * @return UserAccount|null
     */
    public function find(string $userAccountId): ?UserAccount
    {
        return $this
            ->createQueryBuilder('ua')
            ->where('ua.id = :userAccountId')
            ->setParameter('userAccountId', $userAccountId)
            ->getQuery()->getOneOrNullResult();
    }
}
