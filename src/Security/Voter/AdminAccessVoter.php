<?php

namespace App\Security\Voter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * A voter for granting user to access administration pages.
 */
class AdminAccessVoter extends Voter
{
    public const ATTRIBUTE = 'admin-access';

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * AdminVoter constructor.
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
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
        return
            $this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') &&
            $this->hasRole($token, 'ROLE_SIMPLESSO_ADMIN');
    }
}
