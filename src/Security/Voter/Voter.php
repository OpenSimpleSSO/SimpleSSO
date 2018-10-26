<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter as BaseVoter;

abstract class Voter extends BaseVoter
{
    /**
     * @param TokenInterface $token
     * @param string         $role
     * @return bool
     */
    protected function hasRole(TokenInterface $token, string $role)
    {
        foreach ($token->getRoles() as $tokenRole) {
            if ($tokenRole->getRole() === $role) {
                return true;
            }
        }

        return false;
    }
}
