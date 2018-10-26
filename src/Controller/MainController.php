<?php

namespace App\Controller;

use SimpleSSO\CommonBundle\Model\OpenSslModel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Vinorcola\HelperBundle\Controller;

/**
 * @Route(name="main.")
 */
class MainController extends Controller
{
    /**
     * @Route("", methods={"GET"}, name="home")
     *
     * @return Response
     */
    public function home(): Response
    {
        return $this->redirectToRoute('userProfile.show', [], 301); // Moved permanently
    }

    /**
     * @Route("/public-key", methods={"GET"}, name="publicKey")
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
