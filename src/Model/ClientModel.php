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
        $client->url = $data->url;
        $client->redirectPath = $data->redirectPath[0] === '/' ? $data->redirectPath : '/' . $data->redirectPath;
        $client->logoutPath = $data->logoutPath[0] === '/' ? $data->logoutPath : '/' . $data->logoutPath;
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
        $data->url = $client->url;
        $data->redirectPath = $client->redirectPath;
        $data->logoutPath = $client->logoutPath;

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
        $client->url = $data->url;
        $client->redirectPath = $data->redirectPath[0] === '/' ? $data->redirectPath : '/' . $data->redirectPath;
        $client->logoutPath = $data->logoutPath[0] === '/' ? $data->logoutPath : '/' . $data->logoutPath;
        $this->repository->save($client);
    }

    /**
     * @param Client $client
     */
    public function delete(Client $client): void
    {
        $this->repository->remove($client);
    }
}
