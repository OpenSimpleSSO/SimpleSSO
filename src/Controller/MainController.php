<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SimpleSSO\CommonBundle\Model\OpenSslModel;
use Symfony\Component\HttpFoundation\Response;
use Vinorcola\HelperBundle\Controller;

/**
 * @Route(name="main.")
 */
class MainController extends Controller
{
    /**
     * @Route("", name="home")
     * @Method("GET")
     *
     * @return Response
     */
    public function home(): Response
    {
        return $this->redirectToRoute('userProfile.show', [], 301); // Moved permanently
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
