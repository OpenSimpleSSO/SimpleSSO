<?php

namespace App\Security\Voter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ClientAccessVoter extends Voter
{
    public const ATTRIBUTE = 'client-access';

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
        return $this->hasRole($token, 'ROLE_CLIENT');
    }
}
