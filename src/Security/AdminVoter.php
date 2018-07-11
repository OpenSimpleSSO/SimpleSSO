<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AdminVoter extends Voter
{
    public const ATTRIBUTE = 'ADMIN_ACCESS';

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

    /**
     * @param TokenInterface $token
     * @param string         $role
     * @return bool
     */
    private function hasRole(TokenInterface $token, string $role)
    {
        foreach ($token->getRoles() as $tokenRole) {
            if ($tokenRole->getRole() === $role) {
                return true;
            }
        }

        return false;
    }
}
