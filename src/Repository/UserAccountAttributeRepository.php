<?php

namespace App\Repository;

use App\Entity\UserAccountAttribute;
use Vinorcola\HelperBundle\Repository;

class UserAccountAttributeRepository extends Repository
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass(): string
    {
        return UserAccountAttribute::class;
    }

    /**
     * @param UserAccountAttribute $attribute
     */
    public function save(UserAccountAttribute $attribute): void
    {
        $this->entityManager->persist($attribute);
    }

    /**
     * @return UserAccountAttribute[]
     */
    public function findAll(): array
    {
        return $this
            ->createQueryBuilder('uaa')
            ->orderBy('uaa.key', 'ASC')
            ->getQuery()->getResult();
    }

    /**
     * @param string $userAccountAttributeId
     * @return UserAccountAttribute|null
     */
    public function find(string $userAccountAttributeId): ?UserAccountAttribute
    {
        return $this
            ->createQueryBuilder('uaa')
            ->where('uaa.id = :userAccountAttributeId')
            ->setParameter('userAccountAttributeId', $userAccountAttributeId)
            ->getQuery()->getOneOrNullResult();
    }
}
