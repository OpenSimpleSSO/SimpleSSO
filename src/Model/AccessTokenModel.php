<?php

namespace App\Model;

use App\Entity\Client;
use SimpleSSO\CommonBundle\Exception\InvalidTokenException;
use SimpleSSO\CommonBundle\Model\Data\SignedToken;
use SimpleSSO\CommonBundle\Model\TokenModel;
use Symfony\Component\Cache\Adapter\AdapterInterface;

/**
 * A model for manipulating access tokens.
 */
class AccessTokenModel
{
    private const CACHE_PREFIX = 'access-token.';
    private const REQUIRED_ATTRIBUTES = [
        'time',
        'nonce',
    ];

    /**
     * @var TokenModel
     */
    private $tokenModel;

    /**
     * @var AdapterInterface
     */
    private $cache;

    /**
     * AccessTokenModel constructor.
     *
     * @param TokenModel       $tokenModel
     * @param AdapterInterface $cache
     */
    public function __construct(
        TokenModel $tokenModel,
        AdapterInterface $cache
    ) {
        $this->tokenModel = $tokenModel;
        $this->cache = $cache;
    }

    /**
     * Extract the data from the access token if it is valid.
     *
     * @param SignedToken $token
     * @param Client      $client
     * @return array
     */
    public function getAccessTokenData(SignedToken $token, Client $client): array
    {
        $data = $this->tokenModel->receiveToken($token, $client->publicKey, self::REQUIRED_ATTRIBUTES);
        $this->checkTokenUniqueUsage($client->getId(), $data);

        return $data;
    }

    /**
     * @param string $clientId
     * @param array  $data
     * @throws InvalidTokenException when the token is missing the "nonce" attribute or when it has already been consumed.
     */
    private function checkTokenUniqueUsage(string $clientId, array $data): void
    {
        $tokenCacheItem = $this->cache->getItem(self::CACHE_PREFIX . $clientId . '.' . $data['nonce']);
        if ($tokenCacheItem->isHit()) {
            throw new InvalidTokenException('Token already consumed.');
        }
        $tokenCacheItem->set($data);
        $tokenCacheItem->expiresAt($data['expire']);
        $this->cache->save($tokenCacheItem);
    }
}
