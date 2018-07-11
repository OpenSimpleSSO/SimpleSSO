<?php

namespace App\Controller\Admin;

use App\Repository\UserAccountRepository;
use Symfony\Component\HttpFoundation\Response;
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
}
