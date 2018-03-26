<?php

namespace App\Model;

use App\Entity\Client;
use App\Model\Data\Admin\Client\CreateEditClient;
use App\Repository\ClientRepository;

class ClientModel
{
    /**
     * @var ClientRepository
     */
    private $repository;

    /**
     * ClientModel constructor.
     *
     * @param ClientRepository $repository
     */
    public function __construct(ClientRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param CreateEditClient $data
     * @return Client
     */
    public function create(CreateEditClient $data): Client
    {
        $client = new Client();
        $client->title = $data->title;
        $client->publicKey = $data->publicKey;
        $client->redirectUrl = $data->redirectUrl;
        $this->repository->save($client);

        return $client;
    }

    /**
     * @param Client $client
     * @return CreateEditClient
     */
    public function generateEditionData(Client $client): CreateEditClient
    {
        $data = new CreateEditClient();
        $data->title = $client->title;
        $data->publicKey = $client->publicKey;
        $data->redirectUrl = $client->redirectUrl;

        return $data;
    }

    /**
     * @param Client           $client
     * @param CreateEditClient $data
     */
    public function edit(Client $client, CreateEditClient $data): void
    {
        $client->title = $data->title;
        $client->publicKey = $data->publicKey;
        $client->redirectUrl = $data->redirectUrl;
    }

    /**
     * @param Client $client
     */
    public function delete(Client $client): void
    {
        $this->repository->remove($client);
    }
}
