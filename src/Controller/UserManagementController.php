<?php

namespace App\Controller;

use App\Form\UserManagement\RegistrationType;
use App\Model\AuthTokenModel;
use App\Model\EmailModel;
use App\Model\UserAccountModel;
use App\Repository\ClientRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use LogicException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Vinorcola\HelperBundle\Controller;

/**
 * @Route(name="userManagement.")
 */
class UserManagementController extends Controller
{
    private const SESSION_CLIENT_ID = 'authentication.client-id';
    private const SESSION_ACCESS_TOKEN_DATA = 'authentication.access-token-data';

    /**
     * @Route("/authenticate", name="authenticate")
     * @Method("GET")
     *
     * @param SessionInterface      $session
     * @param TokenStorageInterface $tokenStorage
     * @param Request               $request
     * @return Response
     */
    public function authenticate(
        SessionInterface $session,
        TokenStorageInterface $tokenStorage,
        Request $request
    ): Response {

        $symfonySecurityToken = $tokenStorage->getToken();
        if (!$this->isGranted('CLIENT_ACCESS', $request)) {
            if ($symfonySecurityToken->hasAttribute('client')) {
                throw new AccessDeniedHttpException('Access token invalid.');
            }
            throw new AccessDeniedHttpException('No client authentication provided.');
        }

        $session->set(self::SESSION_CLIENT_ID, $symfonySecurityToken->getAttribute('client')->getId());
        $session->set(self::SESSION_ACCESS_TOKEN_DATA, $symfonySecurityToken->getAttribute('access-token'));

        return $this->redirectToRoute('userManagement.generateAuthToken');
    }

    /**
     * @Route("/generate-auth-token", name="generateAuthToken")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     *
     * @param SessionInterface $session
     * @param ClientRepository $clientRepository
     * @param AuthTokenModel   $authTokenModel
     * @return Response
     */
    public function generateAuthToken(
        SessionInterface $session,
        ClientRepository $clientRepository,
        AuthTokenModel $authTokenModel
    ): Response {

        if (!$session->has(self::SESSION_CLIENT_ID) || !$session->has(self::SESSION_ACCESS_TOKEN_DATA)) {
            return $this->redirectToRoute('userManagement.authenticate');
        }

        $client = $clientRepository->find($session->get(self::SESSION_CLIENT_ID));
        $session->remove(self::SESSION_CLIENT_ID);
        if (!$client) {
            throw new NotFoundHttpException();
        }
        $accessToken = $session->get(self::SESSION_ACCESS_TOKEN_DATA);
        $session->remove(self::SESSION_ACCESS_TOKEN_DATA);
        $authToken = $authTokenModel->generate($this->getUser(), $client, $accessToken['nonce']);

        return $this->redirect($client->redirectUrl . '?' . $authToken->getAsUrlParameters());
    }

    /**
     * @Route("/register", name="register")
     * @Method({"GET", "POST"})
     *
     * @param Request          $request
     * @param UserAccountModel $model
     * @param EmailModel       $emailModel
     * @return Response
     */
    public function register(Request $request, UserAccountModel $model, EmailModel $emailModel): Response
    {
        $form = $this->createForm(RegistrationType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $userAccount = $model->create($form->getData());
                $this->saveDatabase();
                $model->forceAuthentication($userAccount);
                $emailModel->sendRegistrationEmail($userAccount);

                return $this->redirectToRoute('userProfile.show');
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
    public function logout(): void
    {
        throw new LogicException('Logout is handled by Symfony. This action should not be called. It is only defined in order to define the route.');
    }
}
