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
     * @return UserAccount[]
     */
    public function findAll(): array
    {
        return $this
            ->createQueryBuilder('ua')
            ->orderBy('ua.firstName', 'ASC')
            ->addOrderBy('ua.lastName', 'ASC')
            ->getQuery()->getResult();
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

    /**
     * @param string $emailAddress
     * @return UserAccount|null
     */
    public function findByEmailAddress(string $emailAddress): ?UserAccount
    {
        return $this
            ->createQueryBuilder('ua')
            ->where('ua.emailAddress = :emailAddress')
            ->setParameter('emailAddress', $emailAddress)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param string $userAccountId
     * @return string|null
     */
    public function findVersion(string $userAccountId): ?string
    {
        $result = $this
            ->createQueryBuilder('ua')->select('ua.version')
            ->where('ua.id = :userAccountId')
            ->setParameter('userAccountId', $userAccountId)
            ->getQuery()->getOneOrNullResult();
        if (!$result) {
            return null;
        }

        return $result['version'];
    }
}
