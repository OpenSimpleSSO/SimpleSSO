<?php

namespace App\Controller\Admin;

use App\Model\EmailModel;
use App\Model\UserAccountAttributeModel;
use App\Model\UserAccountModel;
use App\Repository\UserAccountRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Vinorcola\HelperBundle\Controller;

/**
 * @Route("/admin/user-account", name="admin.userAccount.")
 */
class UserAccountController extends Controller
{
    /**
     * @Route("", methods={"GET"}, name="list")
     *
     * @param UserAccountRepository $repository
     * @return Response
     */
    public function list(UserAccountRepository $repository): Response
    {
        $userAccounts = $repository->findAll();

        return $this->render('Admin/UserAccount/list.html.twig', [
            'userAccounts' => $userAccounts,
        ]);
    }

    /**
     * @Route("/profile-{userAccountId}", methods={"GET"}, name="profile", requirements={
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

        return $this->render('Admin/UserAccount/profile.html.twig', [
            'userAccount' => $userAccount,
            'attributes'  => $attributeModel->get(),
        ]);
    }

    /**
     * @Route("/profile-{userAccountId}/send-verification-email", methods={"GET"}, name="sendVerificationEmail", requirements={
     *     "userAccountId": "^[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}$",
     * })
     *
     * @param string                $userAccountId
     * @param UserAccountRepository $repository
     * @param UserAccountModel      $model
     * @param EmailModel            $emailModel
     * @return Response
     */
    public function sendVerificationEmail(
        string $userAccountId,
        UserAccountRepository $repository,
        UserAccountModel $model,
        EmailModel $emailModel
    ): Response {

        $userAccount = $repository->find($userAccountId);
        if (!$userAccount) {
            throw new NotFoundHttpException();
        }
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

        return $this->redirectToRoute('admin.userAccount.profile', [
            'userAccountId' => $userAccountId,
        ]);
    }
}
