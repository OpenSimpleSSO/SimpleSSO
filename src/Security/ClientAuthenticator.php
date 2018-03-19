<?php

namespace App\Security;

use App\Entity\Client;
use App\Model\AccessTokenModel;
use SimpleSSO\CommonBundle\Exception\InvalidTokenException;
use SimpleSSO\CommonBundle\Model\Data\SignedToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Vinorcola\ApiServerTools\Response;

class ClientAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var AccessTokenModel
     */
    private $model;

    /**
     * ClientAuthenticator constructor.
     *
     * @param AccessTokenModel $model
     */
    public function __construct(AccessTokenModel $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request)
    {
        return
            $request->headers->has('SSSO-Client') &&
            $request->headers->has('SSSO-Access-Token') &&
            $request->headers->has('SSSO-Access-Token-Signature');
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(Request $request)
    {
        return [
            'clientId'  => $request->headers->get('SSSO-Client'),
            'token'     => $request->headers->get('SSSO-Access-Token'),
            'signature' => $request->headers->get('SSSO-Access-Token-Signature'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (!preg_match('/^[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}$/', $credentials['clientId'])) {
            return null;
        }
        return $userProvider->loadUserByUsername($credentials['clientId']);
    }

    /**
     * {@inheritdoc}
     * @param Client $user
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        try {
            $user->currentTokenData = $this->model->getAccessTokenData(
                new SignedToken($credentials['token'], $credentials['signature']),
                $user
            );
        } catch (InvalidTokenException $exception) {
            throw new AuthenticationException('Invalid access token.', 0, $exception);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $message = $exception->getMessage();
        if ($exception->getPrevious()) {
            $message .= ' ' . $exception->getPrevious()->getMessage();
        }

        return new Response([], $message, 403);
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        throw new AccessDeniedHttpException('No authentication data provided.', $authException);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return false;
    }
}
