<?php

namespace App\Controller;

use App\Entity\CategoryProduct;
use App\Entity\Product;
use App\Form\CategoryProductType;
use App\Repository\CategoryProductRepository;
use App\Service\ImageCropUpload;
use Cocur\Slugify\Slugify;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/cpanel/category/product")
 */
class CategoryProductController extends AbstractController
{
    /**
     * @Route("/", name="category_product_index", methods={"GET"})
     */
    public function index(CategoryProductRepository $categoryProductRepository): Response
    {
        return $this->render('category_product/index.html.twig', [
            'category_products' => $categoryProductRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="category_product_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $categoryProduct = new CategoryProduct();
        $form = $this->createForm(CategoryProductType::class, $categoryProduct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($categoryProduct);
            $entityManager->flush();

            $this->addFlash('success', 'Se ha guardado satisfactoriamente');

            return $this->redirectToRoute('category_product_index');
        }

        return $this->render('category_product/new.html.twig', [
            'category_product' => $categoryProduct,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="category_product_show", methods={"GET"})
     */
    public function show(CategoryProduct $categoryProduct): Response
    {
        return $this->render('category_product/show.html.twig', [
            'category_product' => $categoryProduct,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="category_product_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, CategoryProduct $categoryProduct): Response
    {
        $form = $this->createForm(CategoryProductType::class, $categoryProduct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Se ha guardado satisfactoriamente');

            return $this->redirectToRoute('category_product_index');
        }

        return $this->render('category_product/edit.html.twig', [
            'category_product' => $categoryProduct,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="category_product_delete", methods={"DELETE"})
     */
    public function delete(Request $request, CategoryProduct $categoryProduct): Response
    {
        if ($this->isCsrfTokenValid('delete'.$categoryProduct->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($categoryProduct);
            $entityManager->flush();
        }

        return $this->redirectToRoute('category_product_index');
    }

    /**
     * @Route("/cropper/{id}/", name="category_producto_cropper", methods={"GET","POST"})
     * @param Request $request
     * @param CategoryProduct $categoryProduct
     * @param ImageCropUpload $imageCropUpload
     * @return Response
     */
    public function subirFoto(Request $request, CategoryProduct $categoryProduct, ImageCropUpload $imageCropUpload): Response
    {
        if ($request->isXmlHttpRequest()) {
            if ($request->getMethod() !== 'POST') {
                return new JsonResponse('Método invalido',400);
            }

            if ($categoryProduct->getImage()) {
                $imageName = $categoryProduct->getImage();
            } else {

                $imageName = strtolower(Slugify::create()->slugify($categoryProduct->getImage()));
                $imageName .= uniqid('-',false);
            }

            $imageString = json_decode($request->getContent());

            $entityManager = $this->getDoctrine()->getManager();

            return $imageCropUpload->subirImagen($imageString, $imageName, $categoryProduct, $entityManager);
        }

        return $this->render('category_product/cropper.html.twig', [
            'foto' => $categoryProduct->getImage(),
            'nombre' => $categoryProduct->getImage(),
            'cancelUrl' => $this->generateUrl('category_product_show', [ 'id' => $categoryProduct->getId() ]),
        ]);
    }

    /**
     * @param Request $request
     * @param CategoryProduct $categoryProduct
     * @Route("/active/{id}", name="category_product_active")
     */
    public function activeCategoryProduct(Request $request, CategoryProduct $categoryProduct)
    {
        if (!$request->isXmlHttpRequest()){
            throw $this->createNotFoundException();
        }

        $categoryProduct->getActive() ? $categoryProduct->setActive(0) : $categoryProduct->setActive(1);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($categoryProduct);

        try {
            $entityManager->flush();

            return new JsonResponse(['message' => 'Se ha cambiado el estado de la categoria satisfactoriamente', 'public' => $categoryProduct->getActive()]);
        }catch (\Exception $exception){
            return new JsonResponse('Ha ocurrido un error '.$exception->getMessage());
        }
    }
}
