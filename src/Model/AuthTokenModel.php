<?php

namespace App\Model;

use App\Entity\Client;
use App\Entity\UserAccount;
use SimpleSSO\CommonBundle\Model\Data\SignedToken;
use SimpleSSO\CommonBundle\Model\TokenModel;

class AuthTokenModel
{
    private const EXPIRATION_INTERVAL = 'PT5M';

    /**
     * @var TokenModel
     */
    private $tokenModel;

    /**
     * AuthTokenModel constructor.
     *
     * @param TokenModel $tokenModel
     */
    public function __construct(TokenModel $tokenModel)
    {
        $this->tokenModel = $tokenModel;
    }

    /**
     * Generate an auth token for the current user and the given client.
     *
     * @param UserAccount $userAccount
     * @param Client      $client
     * @param string      $nonce
     * @return SignedToken
     */
    public function generate(UserAccount $userAccount, Client $client, string $nonce): SignedToken
    {
        return $this->tokenModel->emitToken([
            'userId' => $userAccount->getId(),
            'nonce'  => $nonce,
            'expire' => $this->tokenModel
                ->getExpirationDate(self::EXPIRATION_INTERVAL)
                ->format(DATE_ISO8601),
        ], $client->publicKey);
    }
}
