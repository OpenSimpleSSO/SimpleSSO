<?php

namespace App\Controller\Api;

use App\Entity\UserAccount;
use App\Model\Data\Api\User\PasswordChange;
use App\Model\Data\Api\User\ProfileEdition;
use App\Model\Data\Api\User\Registration;
use App\Model\EmailModel;
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

        return $this->generateProfileResponse($userAccount);
    }

    /**
     * @Route("/register", name="register")
     * @Method("POST")
     * @Security("is_granted('ROLE_CLIENT')")
     *
     * @param Request          $request
     * @param JsonRequestModel $jsonRequestModel
     * @param UserAccountModel $model
     * @param EmailModel       $emailModel
     * @return Response
     */
    public function register(
        Request $request,
        JsonRequestModel $jsonRequestModel,
        UserAccountModel $model,
        EmailModel $emailModel
    ): Response {

        $data = $jsonRequestModel->handleRequest($request, Registration::class);
        /** @var ConstraintViolationListInterface $errors */
        if ($jsonRequestModel->isValid($data, $errors)) {
            try {
                $userAccount = $model->create($data);
                $this->saveDatabase();
                $emailModel->sendRegistrationEmail($userAccount);

                return new Response([
                    'id' => $userAccount->getId(),
                ], null, 201);
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

    /**
     * @Route("/{userAccountId}", name="editProfile", requirements={
     *     "userAccountId": "^[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}$",
     * })
     * @Method("PUT")
     * @Security("is_granted('ROLE_CLIENT')")
     *
     * @param Request               $request
     * @param string                $userAccountId
     * @param UserAccountRepository $repository
     * @param JsonRequestModel      $jsonRequestModel
     * @param UserAccountModel      $model
     * @param EmailModel            $emailModel
     * @return Response
     */
    public function editProfile(
        Request $request,
        string $userAccountId,
        UserAccountRepository $repository,
        JsonRequestModel $jsonRequestModel,
        UserAccountModel $model,
        EmailModel $emailModel
    ): Response {

        $userAccount = $repository->find($userAccountId);
        if (!$userAccount) {
            throw new NotFoundHttpException('User account not found.');
        }
        $initialEmailAddress = $userAccount->emailAddress;

        /** @var ProfileEdition $data */
        $data = $jsonRequestModel->handleRequest($request, ProfileEdition::class);
        /** @var ConstraintViolationListInterface $errors */
        if ($jsonRequestModel->isValid($data, $errors)) {
            try {
                $model->editProfile($userAccount, $data);
                $this->saveDatabase();
                if ($userAccount->emailAddress !== $initialEmailAddress) {
                    $emailModel->sendEmailAddressVerificationEmail($userAccount);
                }

                return $this->generateProfileResponse($userAccount);
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

    /**
     * @Route("/{userAccountId}/password", name="changePassword", requirements={
     *     "userAccountId": "^[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}$",
     * })
     * @Method("PUT")
     * @Security("is_granted('ROLE_CLIENT')")
     *
     * @param Request               $request
     * @param string                $userAccountId
     * @param UserAccountRepository $repository
     * @param JsonRequestModel      $jsonRequestModel
     * @param UserAccountModel      $model
     * @return Response
     */
    public function changePassword(
        Request $request,
        string $userAccountId,
        UserAccountRepository $repository,
        JsonRequestModel $jsonRequestModel,
        UserAccountModel $model
    ): Response {

        $userAccount = $repository->find($userAccountId);
        if (!$userAccount) {
            throw new NotFoundHttpException('User account not found.');
        }

        $data = $jsonRequestModel->handleRequest($request, PasswordChange::class);
        /** @var ConstraintViolationListInterface $errors */
        if ($jsonRequestModel->isValid($data, $errors)) {
            $model->changePassword($userAccount, $data);
            $this->saveDatabase();

            return new Response();
        }

        throw new InvalidInputException('Invalid request.', $errors);
    }

    /**
     * @Route("/{userAccountId}/enable", name="enable", requirements={
     *     "userAccountId": "^[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}$",
     * })
     * @Method("POST")
     * @Security("is_granted('ROLE_CLIENT')")
     *
     * @param string                $userAccountId
     * @param UserAccountRepository $repository
     * @param UserAccountModel      $model
     * @return Response
     */
    public function enable(
        string $userAccountId,
        UserAccountRepository $repository,
        UserAccountModel $model
    ): Response {

        $userAccount = $repository->find($userAccountId);
        if (!$userAccount) {
            throw new NotFoundHttpException('User account not found.');
        }

        $model->enable($userAccount);
        $this->saveDatabase();

        return $this->generateProfileResponse($userAccount);
    }

    /**
     * @Route("/{userAccountId}/disable", name="disable", requirements={
     *     "userAccountId": "^[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}$",
     * })
     * @Method("POST")
     * @Security("is_granted('ROLE_CLIENT')")
     *
     * @param string                $userAccountId
     * @param UserAccountRepository $repository
     * @param UserAccountModel      $model
     * @return Response
     */
    public function disable(
        string $userAccountId,
        UserAccountRepository $repository,
        UserAccountModel $model
    ): Response {

        $userAccount = $repository->find($userAccountId);
        if (!$userAccount) {
            throw new NotFoundHttpException('User account not found.');
        }

        $model->disable($userAccount);
        $this->saveDatabase();

        return $this->generateProfileResponse($userAccount);
    }

    /**
     * Generate a response containing the user account's profile.
     *
     * @param UserAccount $userAccount
     * @return Response
     */
    private function generateProfileResponse(UserAccount $userAccount): Response
    {
        return new Response([
            'id'                   => $userAccount->getId(),
            'organization'         => $userAccount->organization,
            'emailAddress'         => $userAccount->emailAddress,
            'emailAddressVerified' => $userAccount->emailAddressVerified,
            'firstName'            => $userAccount->firstName,
            'lastName'             => $userAccount->lastName,
            'roles'                => $userAccount->roles,
            'enabled'              => $userAccount->enabled,
        ]);
    }
}
