<?php

namespace App\Controller;

use App\Entity\UserAccount;
use App\Form\UserProfile\ChangePasswordType;
use App\Form\UserProfile\ProfileEditionType;
use App\Model\Data\UserProfile\ChangePassword;
use App\Model\EmailModel;
use App\Model\UserAccountAttributeModel;
use App\Model\UserAccountModel;
use App\Repository\UserAccountRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Vinorcola\HelperBundle\Controller;

/**
 * @Route("/profile", name="userProfile.")
 */
class UserProfileController extends Controller
{
    /**
     * @Route("", methods={"GET"}, name="show")
     * @Security("is_granted('authenticated-user')")
     *
     * @param UserAccountAttributeModel $attributeModel
     * @return Response
     */
    public function show(UserAccountAttributeModel $attributeModel): Response
    {
        return $this->render('UserProfile/show.html.twig', [
            'userAccount' => $this->getUser(),
            'attributes'  => $attributeModel->get(),
        ]);
    }

    /**
     * @Route("/edit", methods={"GET", "POST"}, name="edit")
     * @Security("is_granted('authenticated-user')")
     *
     * @param Request          $request
     * @param UserAccountModel $model
     * @param EmailModel       $emailModel
     * @return Response
     */
    public function edit(
        Request $request,
        UserAccountModel $model,
        EmailModel $emailModel
    ): Response {

        /** @var UserAccount $userAccount */
        $userAccount = $this->getUser();
        $initialEmailAddress = $userAccount->getEmailAddress();

        $form = $this->createForm(ProfileEditionType::class, $model->generateProfileEditionData($userAccount));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $model->editProfile($userAccount, $form->getData());
                $this->saveDatabase();
                if ($userAccount->getEmailAddress() !== $initialEmailAddress) {
                    $emailModel->sendEmailAddressVerificationEmail($userAccount);
                }
                $this->addSuccessMessage();

                return $this->redirectToRoute('userProfile.show');
            } catch (UniqueConstraintViolationException $exception) {
                // Revert email address to avoid security token change, and so user logout.
                $userAccount->setEmailAddress($initialEmailAddress);
                $this->addFormError($form['emailAddress'], 'userAccount.emailAddress.alreadyUsed');
            }
        }

        return $this->render('UserProfile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/send-verification-email", methods={"GET"}, name="sendVerificationEmail")
     * @Security("is_granted('authenticated-user')")
     *
     * @param UserAccountModel $model
     * @param EmailModel       $emailModel
     * @return Response
     */
    public function sendVerificationEmail(
        UserAccountModel $model,
        EmailModel $emailModel
    ): Response {

        /** @var UserAccount $userAccount */
        $userAccount = $this->getUser();
        if ($userAccount->isEmailAddressVerified()) {
            // Do not send email if the address has already been verified.
            throw new BadRequestHttpException();
        }
        $model->generateToken($userAccount);
        $this->saveDatabase();
        $emailModel->sendEmailAddressVerificationEmail($userAccount);
        $this->addSuccessMessage([
            'emailAddress' => $userAccount->getEmailAddress(),
        ]);

        return $this->redirectToRoute('userProfile.show');
    }

    /**
     * @Route("/confirm-email-address/{token}", methods={"GET"}, name="confirmEmailAddress", requirements={
     *     "token": "^[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}$",
     * })
     *
     * @param string                $token
     * @param UserAccountRepository $repository
     * @param UserAccountModel      $model
     * @return Response
     */
    public function confirmEmailAddress(
        string $token,
        UserAccountRepository $repository,
        UserAccountModel $model
    ): Response {
        $userAccount = $repository->findByToken($token);
        if (!$userAccount) {
            throw new NotFoundHttpException();
        }

        $model->verifyEmailAddress($userAccount);
        $model->forceAuthentication($userAccount);
        $this->saveDatabase();

        return $this->render('UserProfile/confirmEmailAddress.html.twig', [
            'userAccount' => $userAccount,
        ]);
    }

    /**
     * @Route("/change-password", methods={"GET", "POST"}, name="changePassword")
     * @Security("is_granted('authenticated-user')")
     *
     * @param Request          $request
     * @param UserAccountModel $model
     * @return Response
     */
    public function changePassword(Request $request, UserAccountModel $model): Response
    {
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            /** @var UserAccount $userAccount */
            $userAccount = $this->getUser();
            /** @var ChangePassword $data */
            $data = $form->getData();
            $currentPasswordValid = $model->isPasswordValid($userAccount, $data->currentPassword);
            if ($form->isValid() && $currentPasswordValid) {
                $model->changePassword($userAccount, $data);
                $this->saveDatabase();
                $this->addSuccessMessage();

                return $this->redirectToRoute('userProfile.show');
            }
            if (!$currentPasswordValid) {
                $this->addFormError($form['currentPassword'], 'userAccount.password.invalidPassword');
            }
        }

        return $this->render('UserProfile/changePassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
