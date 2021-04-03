<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DashboardController
 * @package App\Controller
 * @Route("/cpanel")
 */
class DashboardController extends AbstractController
{
    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'products' => $productRepository->findBy([], ['visita' => 'DESC'], 3)
        ]);
    }
}
