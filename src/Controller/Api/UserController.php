<?php

namespace App\Controller\Api;

use App\Entity\UserAccount;
use App\Entity\UserAccountAttribute;
use App\Model\Data\Api\User\PasswordChange;
use App\Model\Data\Api\User\ProfileEdition;
use App\Model\Data\Api\User\Registration;
use App\Model\EmailModel;
use App\Model\JsonRequestModel;
use App\Model\UserAccountAttributeModel;
use App\Model\UserAccountModel;
use App\Repository\UserAccountRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
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
     * @Route("/{userAccountId}", methods={"GET"}, name="profile", requirements={
     *     "userAccountId": "^[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}$",
     * })
     *
     * @param string                    $userAccountId
     * @param UserAccountRepository     $repository
     * @param UserAccountAttributeModel $attributeModel
     * @return Response
     */
    public function profile(
        string $userAccountId,
        UserAccountRepository $repository,
        UserAccountAttributeModel $attributeModel
    ): Response {

        $userAccount = $repository->find($userAccountId);
        if (!$userAccount) {
            throw new NotFoundHttpException();
        }

        return $this->generateProfileResponse($userAccount, $attributeModel->get());
    }

    /**
     * @Route("/{userAccountId}/check-version/{version}", methods={"GET"}, name="checkVersion", requirements={
     *     "userAccountId": "^[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}$",
     *     "version":       "^[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}$",
     * })
     *
     * @param string                $userAccountId
     * @param string                $version
     * @param UserAccountRepository $repository
     * @return Response
     */
    public function checkVersion(string $userAccountId, string $version, UserAccountRepository $repository): Response
    {
        $lastVersion = $repository->findVersion($userAccountId);
        if (!$lastVersion) {
            throw new NotFoundHttpException();
        }

        return new Response([
            'isLastVersion' => $version === $lastVersion,
        ]);
    }

    /**
     * @Route("/register", methods={"POST"}, name="register")
     *
     * @param Request                   $request
     * @param JsonRequestModel          $jsonRequestModel
     * @param UserAccountModel          $model
     * @param EmailModel                $emailModel
     * @param UserAccountAttributeModel $attributeModel
     * @param TranslatorInterface       $translator
     * @return Response
     */
    public function register(
        Request $request,
        JsonRequestModel $jsonRequestModel,
        UserAccountModel $model,
        EmailModel $emailModel,
        UserAccountAttributeModel $attributeModel,
        TranslatorInterface $translator
    ): Response {

        $data = $jsonRequestModel->handleRequest($request, Registration::class);
        /** @var ConstraintViolationListInterface $errors */
        if ($jsonRequestModel->isValid($data, $errors)) {
            try {
                $userAccount = $model->create($data);
                $this->saveDatabase();
                $emailModel->sendRegistrationEmail($userAccount);

                return $this->generateProfileResponse($userAccount, $attributeModel->get(), 201);
            } catch (UniqueConstraintViolationException $exception) {
                $errors->add(new ConstraintViolation(
                    $translator->trans('userAccount.emailAddress.alreadyUsed', [], 'validators', 'en'),
                    'userAccount.emailAddress.alreadyUsed',
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
     * @Route("/{userAccountId}", methods={"PUT"}, name="editProfile", requirements={
     *     "userAccountId": "^[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}$",
     * })
     *
     * @param Request                   $request
     * @param string                    $userAccountId
     * @param UserAccountRepository     $repository
     * @param UserAccountAttributeModel $attributeModel
     * @param JsonRequestModel          $jsonRequestModel
     * @param UserAccountModel          $model
     * @param EmailModel                $emailModel
     * @param TranslatorInterface       $translator
     * @return Response
     */
    public function editProfile(
        Request $request,
        string $userAccountId,
        UserAccountRepository $repository,
        UserAccountAttributeModel $attributeModel,
        JsonRequestModel $jsonRequestModel,
        UserAccountModel $model,
        EmailModel $emailModel,
        TranslatorInterface $translator
    ): Response {

        $userAccount = $repository->find($userAccountId);
        if (!$userAccount) {
            throw new NotFoundHttpException('User account not found.');
        }
        $initialEmailAddress = $userAccount->getEmailAddress();

        /** @var ProfileEdition $data */
        $data = $jsonRequestModel->handleRequest($request, ProfileEdition::class);
        /** @var ConstraintViolationListInterface $errors */
        if ($jsonRequestModel->isValid($data, $errors)) {
            try {
                $model->editProfile($userAccount, $data);
                $this->saveDatabase();
                if ($userAccount->getEmailAddress() !== $initialEmailAddress) {
                    $emailModel->sendEmailAddressVerificationEmail($userAccount);
                }

                return $this->generateProfileResponse($userAccount, $attributeModel->get());
            } catch (UniqueConstraintViolationException $exception) {
                $errors->add(new ConstraintViolation(
                    $translator->trans('userAccount.emailAddress.alreadyUsed', [], 'validators', 'en'),
                    'userAccount.emailAddress.alreadyUsed',
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
     * @Route("/{userAccountId}/password", methods={"PUT"}, name="changePassword", requirements={
     *     "userAccountId": "^[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}$",
     * })
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
     * @Route("/{userAccountId}/enable", methods={"POST"}, name="enable", requirements={
     *     "userAccountId": "^[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}$",
     * })
     *
     * @param string                    $userAccountId
     * @param UserAccountRepository     $repository
     * @param UserAccountAttributeModel $attributeModel
     * @param UserAccountModel          $model
     * @return Response
     */
    public function enable(
        string $userAccountId,
        UserAccountRepository $repository,
        UserAccountAttributeModel $attributeModel,
        UserAccountModel $model
    ): Response {

        $userAccount = $repository->find($userAccountId);
        if (!$userAccount) {
            throw new NotFoundHttpException('User account not found.');
        }

        $model->enable($userAccount);
        $this->saveDatabase();

        return $this->generateProfileResponse($userAccount, $attributeModel->get());
    }

    /**
     * @Route("/{userAccountId}/disable", methods={"POST"}, name="disable", requirements={
     *     "userAccountId": "^[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}$",
     * })
     *
     * @param string                    $userAccountId
     * @param UserAccountRepository     $repository
     * @param UserAccountAttributeModel $attributeModel
     * @param UserAccountModel          $model
     * @return Response
     */
    public function disable(
        string $userAccountId,
        UserAccountRepository $repository,
        UserAccountAttributeModel $attributeModel,
        UserAccountModel $model
    ): Response {

        $userAccount = $repository->find($userAccountId);
        if (!$userAccount) {
            throw new NotFoundHttpException('User account not found.');
        }

        $model->disable($userAccount);
        $this->saveDatabase();

        return $this->generateProfileResponse($userAccount, $attributeModel->get());
    }

    /**
     * Generate a response containing the user account's profile.
     *
     * @param UserAccount            $userAccount
     * @param UserAccountAttribute[] $attributes
     * @param int                    $status
     * @return Response
     */
    private function generateProfileResponse(UserAccount $userAccount, array $attributes, int $status = 200): Response
    {
        $data = [
            'id'                   => $userAccount->getId(),
            'version'              => $userAccount->getVersion(),
            'emailAddress'         => $userAccount->getEmailAddress(),
            'emailAddressVerified' => $userAccount->isEmailAddressVerified(),
            'firstName'            => $userAccount->getFirstName(),
            'lastName'             => $userAccount->getLastName(),
            'roles'                => $userAccount->getRoles(),
            'enabled'              => $userAccount->isEnabled(),
        ];
        foreach ($attributes as $attribute) {
            $data[$attribute->key] = $userAccount->getAttribute($attribute->key);
        }

        return new Response($data, null, $status);
    }
}
