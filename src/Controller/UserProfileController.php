<?php

namespace App\Controller;

use App\Entity\UserAccount;
use App\Form\UserManagement\ProfileEditionType;
use App\Model\EmailModel;
use App\Model\UserAccountModel;
use App\Repository\UserAccountRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Vinorcola\HelperBundle\Controller;

/**
 * @Route("/profile", name="userProfile.")
 */
class UserProfileController extends Controller
{
    /**
     * @Route("", name="show")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     *
     * @return Response
     */
    public function show(): Response
    {
        return $this->render('UserProfile/show.html.twig', [
            'userAccount' => $this->getUser(),
        ]);
    }

    /**
     * @Route("/edit", name="edit")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_USER')")
     *
     * @param Request          $request
     * @param UserAccountModel $model
     * @param EmailModel       $emailModel
     * @return Response
     */
    public function edit(Request $request, UserAccountModel $model, EmailModel $emailModel): Response
    {
        /** @var UserAccount $userAccount */
        $userAccount = $this->getUser();
        $initialEmailAddress = $userAccount->emailAddress;

        $form = $this->createForm(ProfileEditionType::class, $model->generateProfileEditionData($userAccount));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $model->editProfile($userAccount, $form->getData());
                $this->saveDatabase();
                if ($userAccount->emailAddress !== $initialEmailAddress) {
                    $emailModel->sendEmailAddressVerificationEmail($userAccount);
                }
                $this->addSuccessMessage();

                return $this->redirectToRoute('userProfile.show');
            } catch (UniqueConstraintViolationException $exception) {
                // Revert email address to avoid security token change, and so user logout.
                $userAccount->emailAddress = $initialEmailAddress;
                $this->addFormError($form['emailAddress'], 'userManagement.registration.emailAddress.alreadyUsed');
            }
        }

        return $this->render('UserProfile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/send-verification-email", name="sendVerificationEmail")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
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
        if ($userAccount->emailAddressVerified) {
            // Do not send email if the address has already been verified.
            throw new BadRequestHttpException();
        }
        $model->generateToken($userAccount);
        $this->saveDatabase();
        $emailModel->sendEmailAddressVerificationEmail($userAccount);
        $this->addSuccessMessage([
            'emailAddress' => $userAccount->emailAddress,
        ]);

        return $this->redirectToRoute('userProfile.show');
    }

    /**
     * @Route("/confirm-email-address/{token}", name="confirmEmailAddress", requirements={
     *     "token": "^[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}$",
     * })
     * @Method("GET")
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
}
