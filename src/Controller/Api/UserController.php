<?php

namespace App\Controller\Api;

use App\Repository\UserAccountRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Vinorcola\ApiServerTools\Response;
use Vinorcola\HelperBundle\Controller;

/**
 * @Route("/user", name="api.user.")
 */
class UserController extends Controller
{
    /**
     * @Route("/{userAccountId}", name="profile", requirements={
     *     "userAccountId": "^[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}$",
     * })
     * @Method("GET")
     * @Security("is_granted('ROLE_CLIENT')")
     *
     * @param string                $userAccountId
     * @param UserAccountRepository $repository
     * @return Response
     */
    public function profile(string $userAccountId, UserAccountRepository $repository): Response
    {
        $userAccount = $repository->find($userAccountId);
        if (!$userAccount) {
            throw new NotFoundHttpException();
        }

        return new Response([
            'id'                   => $userAccount->getId(),
            'organization'         => $userAccount->getOrganization(),
            'emailAddress'         => $userAccount->emailAddress,
            'emailAddressVerified' => $userAccount->emailAddressVerified,
            'firstName'            => $userAccount->firstName,
            'lastName'             => $userAccount->lastName,
            'roles'                => $userAccount->roles,
            'enabled'              => $userAccount->enabled,
        ]);
    }
}
