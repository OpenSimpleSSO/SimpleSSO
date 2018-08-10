<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\UserManagement\RegistrationType;
use App\Model\AuthTokenModel;
use App\Model\EmailModel;
use App\Model\UserAccountModel;
use App\Repository\ClientRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use LogicException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
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
    private const SESSION_LOGOUT_STATUS = 'authentication.logout-status';

    private const LOGOUT_STATUS_WAITING = 'waiting';
    private const LOGOUT_STATUS_PROCESSING = 'processing';
    private const LOGOUT_STATUS_LOGGED_OUT = 'logged-out';

    /**
     * @Route("/authenticate", methods={"GET"}, name="authenticate")
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
     * @Route("/generate-auth-token", methods={"GET"}, name="generateAuthToken")
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

        return $this->redirect($client->url . $client->redirectPath . '?' . $authToken->getAsUrlParameters());
    }

    /**
     * @Route("/register", methods={"GET", "POST"}, name="register")
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
                $this->addFormError($form['emailAddress'], 'userAccount.emailAddress.alreadyUsed');
            }
        }

        return $this->render('UserManagement/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/login", methods={"GET", "POST"}, name="login")
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
     * @Route("/logout", name="processLogout")
     *
     * @param SessionInterface $session
     * @param ClientRepository $clientRepository
     * @return Response
     */
    public function processLogout(SessionInterface $session, ClientRepository $clientRepository): Response
    {
        if (!$session->has(self::SESSION_LOGOUT_STATUS)) {
            // Init session with all clients.
            $clients = $clientRepository->findAll();
            $status = array_map(function(Client $client): array {
                return [
                    'client' => $client->getId(),
                    'url'    => $client->url . $client->logoutPath,
                    'status' => self::LOGOUT_STATUS_WAITING,
                ];
            }, $clients);
        } else {
            // Switch processing client to logout.
            $status = $session->get(self::SESSION_LOGOUT_STATUS);
            foreach ($status as &$client) {
                if ($client['status'] === self::LOGOUT_STATUS_PROCESSING) {
                    $client['status'] = self::LOGOUT_STATUS_LOGGED_OUT;
                    break;
                }
            }
        }

        // Find next client to process.
        $nextClient = null;
        foreach ($status as &$client) {
            if ($client['status'] === self::LOGOUT_STATUS_WAITING) {
                $client['status'] = self::LOGOUT_STATUS_PROCESSING;
                $nextClient = $client;
                break;
            }
        }

        // All clients have been logged out, so we logout from the SSO.
        if (!$nextClient) {
            return $this->redirectToRoute('userManagement.logout');
        }

        // Save status.
        $session->set(self::SESSION_LOGOUT_STATUS, $status);

        // Redirect to client logout path.
        return $this->redirect($nextClient['url']);
    }

    /**
     * @Route("/logout-sso", methods={"GET"}, name="logout")
     */
    public function logout(): void
    {
        throw new LogicException('Logout is handled by Symfony. This action should not be called. It is only defined in order to define the route.');
    }
}
