<?php

namespace App\Security;

use App\Model\AccessTokenModel;
use App\Repository\ClientRepository;
use SimpleSSO\CommonBundle\Exception\InvalidTokenException;
use SimpleSSO\CommonBundle\Model\Data\SignedToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ClientVoter extends Voter
{
    public const ATTRIBUTE = 'CLIENT_ACCESS';

    /**
     * @var ClientRepository
     */
    private $clientRepository;

    /**
     * @var AccessTokenModel
     */
    private $model;

    /**
     * ClientVoter constructor.
     *
     * @param ClientRepository $clientRepository
     * @param AccessTokenModel $model
     */
    public function __construct(ClientRepository $clientRepository, AccessTokenModel $model)
    {
        $this->clientRepository = $clientRepository;
        $this->model = $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $attribute === self::ATTRIBUTE && $subject instanceof Request;
    }

    /**
     * {@inheritdoc}
     * @param Request $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        // Check parameter existence.
        if (!$subject->query->has('c') || !$subject->query->has('t') || !$subject->query->has('s')) {
            return false;
        }

        // Fetch client entity.
        $clientId = $subject->query->get('c');
        if (!preg_match('/^[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}$/', $clientId)) {
            return false;
        }
        $client = $this->clientRepository->find($clientId);
        if (!$client) {
            return false;
        }
        $token->setAttribute('client', $client);

        // Decode token.
        try {
            $data = $this->model->getAccessTokenData(SignedToken::FromRequest($subject), $client);
        } catch (InvalidTokenException $exception) {
            return false;
        }
        $token->setAttribute('access-token', $data);

        return true;
    }
}
