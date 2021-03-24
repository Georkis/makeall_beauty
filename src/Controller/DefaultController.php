<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 * @package App\Controller
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function default()
    {
        return $this->redirectToRoute('inicio');
    }
    
    /**
     * @Route("/inicio/", name="inicio")
     */
    public function index(): Response
    {
        return $this->render('default/index.html.twig', [
        ]);
    }
}
