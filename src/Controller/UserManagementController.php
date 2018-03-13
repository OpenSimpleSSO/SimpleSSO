<?php

namespace App\Controller;

use App\Form\UserManagement\RegistrationType;
use App\Model\UserAccountModel;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Vinorcola\HelperBundle\Controller;

/**
 * @Route(name="userManagement.")
 */
class UserManagementController extends Controller
{
    /**
     * @Route("/register", name="registration")
     *
     * @param Request          $request
     * @param UserAccountModel $model
     * @return Response
     */
    public function registration(Request $request, UserAccountModel $model): Response
    {
        $form = $this->createForm(RegistrationType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $userAccount = $model->create($form->getData());
                $this->saveDatabase();
                $model->forceAuthentication($userAccount);

                return $this->redirectToRoute('main.home');
            } catch (UniqueConstraintViolationException $exception) {
                $this->addFormError($form['emailAddress'], 'userManagement.registration.emailAddress.alreadyUsed');
            }
        }

        return $this->render('UserManagement/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/login", name="login")
     * @Method({"GET", "POST"})
     *
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('UserManagement/login.html.twig', [
            'lastEmailAddress' => $authenticationUtils->getLastUsername(),
            'error'            => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     * @Method("GET")
     */
    public function logout()
    {
        throw new \LogicException('Logout is handled by Symfony. This action should not be called. It is only defined in order to get the route.');
    }
}
