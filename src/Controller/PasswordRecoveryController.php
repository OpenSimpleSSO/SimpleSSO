<?php

namespace App\Controller;

use App\Form\PasswordRecovery\PasswordRecoveryType;
use App\Form\PasswordRecovery\RequirePasswordRecoveryType;
use App\Model\EmailModel;
use App\Model\UserAccountModel;
use App\Repository\UserAccountRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Vinorcola\HelperBundle\Controller;

/**
 * @Route("/password-recovery", name="passwordRecovery.")
 */
class PasswordRecoveryController extends Controller
{
    private const SESSION_EMAIL_ADDRESS = 'authentication.email-address';
    private const SESSION_USER_ACCOUNT_ID = 'authentication.user-account-id';

    /**
     * @Route("", methods={"GET", "POST"}, name="start")
     *
     * @param SessionInterface      $session
     * @param Request               $request
     * @param UserAccountRepository $repository
     * @param UserAccountModel      $model
     * @param EmailModel            $emailModel
     * @return Response
     */
    public function start(
        SessionInterface $session,
        Request $request,
        UserAccountRepository $repository,
        UserAccountModel $model,
        EmailModel $emailModel
    ): Response {

        $form = $this->createForm(RequirePasswordRecoveryType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userAccount = $repository->findByEmailAddress($form->getData()->emailAddress);
            if (!$userAccount) {
                $this->addFormError($form['emailAddress'], 'userAccount.emailAddress.notMatchingAnyAccount');
            } else {
                $model->generateToken($userAccount);
                $this->saveDatabase();
                $emailModel->sendPasswordRecoveryEmail($userAccount);
                $session->set(self::SESSION_EMAIL_ADDRESS, $userAccount->emailAddress);

                return $this->redirectToRoute('passwordRecovery.emailSent', [
                    'emailAddress' => $userAccount->emailAddress,
                ]);
            }
        }

        return $this->render('PasswordRecovery/start.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/email-sent/{emailAddress}", methods={"GET"}, name="emailSent")
     *
     * @param string $emailAddress
     * @return Response
     */
    public function emailSent(string $emailAddress): Response
    {
        return $this->render('PasswordRecovery/emailSent.html.twig', [
            'emailAddress' => $emailAddress,
        ]);
    }

    /**
     * @Route("/authenticate/{token}", methods={"GET"}, name="authenticate")
     *
     * @param SessionInterface      $session
     * @param UserAccountRepository $repository
     * @param string                $token
     * @return Response
     */
    public function authenticate(SessionInterface $session, UserAccountRepository $repository, string $token): Response
    {
        if (!$session->has(self::SESSION_EMAIL_ADDRESS)) {
            $this->addErrorMessage([], true);

            return $this->redirectToRoute('passwordRecovery.start');
        }
        $emailAddress = $session->get(self::SESSION_EMAIL_ADDRESS);
        $session->remove(self::SESSION_EMAIL_ADDRESS);
        $userAccount = $repository->findByToken($token);
        if (!$userAccount || $userAccount->emailAddress !== $emailAddress) {
            $this->addErrorMessage([], true);

            return $this->redirectToRoute('passwordRecovery.start');
        }

        $session->set(self::SESSION_USER_ACCOUNT_ID, $userAccount->getId());

        return $this->redirectToRoute('passwordRecovery.changePassword');
    }

    /**
     * @Route("/choose-new-password", methods={"GET", "POST"}, name="changePassword")
     *
     * @param SessionInterface      $session
     * @param Request               $request
     * @param UserAccountRepository $repository
     * @param UserAccountModel      $model
     * @return Response
     */
    public function changePassword(
        SessionInterface $session,
        Request $request,
        UserAccountRepository $repository,
        UserAccountModel $model
    ): Response {

        $userAccountId = $session->get(self::SESSION_USER_ACCOUNT_ID);
        $userAccount = $repository->find($userAccountId);
        if (!$userAccount) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(PasswordRecoveryType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $model->verifyEmailAddress($userAccount); // The process allowed to verify the email address.
            $model->changePassword($userAccount, $form->getData());
            $this->saveDatabase();
            $this->addSuccessMessage();
            $model->forceAuthentication($userAccount);

            return $this->redirectToRoute('main.home');
        }

        return $this->render('PasswordRecovery/changePassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
