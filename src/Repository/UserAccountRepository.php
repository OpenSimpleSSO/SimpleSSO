<?php

namespace App\Repository;

use App\Entity\UserAccount;
use DateTime;
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

    /**
     * @param string $token
     * @return UserAccount|null
     */
    public function findByToken(string $token): ?UserAccount
    {
        return $this
            ->createQueryBuilder('ua')
            ->where('ua.token = :token')
            ->andWhere('ua.tokenExpirationDate > :expirationDate')
            ->setParameter('token', $token)
            ->setParameter('expirationDate', new DateTime())
            ->getQuery()->getOneOrNullResult();
    }
}
