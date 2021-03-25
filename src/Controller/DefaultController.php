<?php

namespace App\Controller;

use App\Entity\CategoryProduct;
use App\Entity\Product;
use App\Repository\CategoryProductRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
    public function index(CategoryProductRepository $categoryProductRepository, ProductRepository $productRepository): Response
    {
        return $this->render('default/index.html.twig', [
            'categories' => $categoryProductRepository->findBy(['active' => true], ['name' => 'ASC']),
            'productos' => $productRepository->findBy(['public' => true], ['visita' => 'DESC'], 8)
        ]);
    }

    public function menuCategory(CategoryProductRepository $categoryProductRepository)
    {
        return $this->render('includes/default_category.html.twig', [
            'categories' => $categoryProductRepository->findBy(['active' => true], ['name' => 'ASC'])
        ]);
    }

    /**
     * @Route("/categoria/producto/{id}", name="default_categoria_producto")
     * @param CategoryProduct $categoryProduct
     * @param ProductRepository $productRepository
     * @return Response
     */
    public function categoriaProductos(CategoryProduct $categoryProduct, ProductRepository $productRepository)
    {
        return $this->render('default/category_products.html.twig', [
            'products' => $productRepository->findBy(['categoryProduct' => $categoryProduct, 'public' => true]),
            'categoria' => $categoryProduct
        ]);
    }

    /**
     * @param Request $request
     * @param Product $product
     * @Route("/like/product/{id}", name="default_like_product")
     */
    public function likePlus(Request $request, Product $product)
    {
        if (!$request->isXmlHttpRequest()){
            throw $this->createNotFoundException();
        }

        $likeCount = $product->getLikeCount() ?? 0;
        $product->setLikeCount($likeCount + 1);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($product);

        try {
            $entityManager->flush();

            return new JsonResponse(1);
        }catch (\Exception $exception){
            return new JsonResponse($exception->getMessage());
        }
    }

    /**
     * @param Product $product
     * @Route("/producto/{id}", name="default_product")
     */
    public function product(Product $product)
    {
        return $this->render('default/product.html.twig', [
            'product' => $product
        ]);
    }
}
