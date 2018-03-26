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
     * @param Client $client
     */
    public function save(Client $client)
    {
        $this->entityManager->persist($client);
    }

    /**
     * @param Client $client
     */
    public function remove(Client $client)
    {
        $this->entityManager->remove($client);
    }

    /**
     * @return Client[]
     */
    public function findAll(): array
    {
        return $this
            ->createQueryBuilder('c')
            ->orderBy('c.title', 'ASC')
            ->getQuery()->getResult();
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
