<?php

namespace App\Repository;

use App\Entity\Client;
use Vinorcola\HelperBundle\Repository;

class ClientRepository extends Repository
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass(): string
    {
        return Client::class;
    }

    /**
     * @param string $clientId
     * @return Client|null
     */
    public function find(string $clientId): ?Client
    {
        return $this
            ->createQueryBuilder('c')
            ->where('c.id = :clientId')
            ->setParameter('clientId', $clientId)
            ->getQuery()->getOneOrNullResult();
    }
}
