<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SimpleSSO\CommonBundle\Model\OpenSslModel;
use Symfony\Component\HttpFoundation\Response;
use Vinorcola\HelperBundle\Controller;

/**
 * @Route(name="main.")
 */
class MainController extends Controller
{
    /**
     * @Route("/home", name="home")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     *
     * @return Response
     */
    public function home(): Response
    {
        return $this->render('Main/home.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    /**
     * @Route("/public-key", name="publicKey")
     * @Method("GET")
     *
     * @param OpenSslModel $securityModel
     * @return Response
     */
    public function publicKey(OpenSslModel $securityModel): Response
    {
        $response = new Response($securityModel->getPublicKey());
        $response->headers->set('Content-Type', 'text/plain');

        return $response;
    }
}
