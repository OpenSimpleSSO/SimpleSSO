<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
}
