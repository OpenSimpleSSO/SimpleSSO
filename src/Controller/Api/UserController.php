<?php

namespace App\Controller\Api;

use App\Model\Data\Api\User\Registration;
use App\Model\JsonRequestModel;
use App\Model\UserAccountModel;
use App\Repository\UserAccountRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Vinorcola\ApiServerTools\Response;
use Vinorcola\HelperBundle\Controller;
use Vinorcola\ApiServerTools\InvalidInputException;

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

    /**
     * @Route("/register", name="register")
     * @Method("POST")
     * @Security("is_granted('ROLE_CLIENT')")
     *
     * @param Request          $request
     * @param JsonRequestModel $jsonRequestModel
     * @param UserAccountModel $model
     * @return Response
     */
    public function register(
        Request $request,
        JsonRequestModel $jsonRequestModel,
        UserAccountModel $model
    ): Response {

        $data = $jsonRequestModel->handleRequest($request, Registration::class);
        /** @var ConstraintViolationListInterface $errors */
        if ($jsonRequestModel->isValid($data, $errors)) {
            try {
                $userAccount = $model->create($data);
                $this->saveDatabase();

                return new Response([
                    'id' => $userAccount->getId(),
                ]);
            } catch (UniqueConstraintViolationException $exception) {
                $errors->add(new ConstraintViolation(
                    'Email address already used.',
                    null,
                    [],
                    $data->emailAddress,
                    'emailAddress',
                    $data->emailAddress
                ));
            }
        }

        throw new InvalidInputException('Invalid request.', $errors);
    }
}
