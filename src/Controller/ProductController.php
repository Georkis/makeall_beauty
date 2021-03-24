<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\ImageCropUpload;
use Cocur\Slugify\Slugify;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/cpanel/product")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/", name="product_index", methods={"GET"})
     */
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="product_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $product->setUserApp($this->getUser());
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Se ha guardado satisfactoriamente');

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="product_show", methods={"GET"})
     */
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="product_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Se ha guardado satisfactoriamente');

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="product_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('product_index');
    }

    /**
     * @Route("/cropper/{id}/", name="producto_cropper", methods={"GET","POST"})
     *
     * @param Request $request
     * @param Producto $producto
     * @param ImageCropUpload $imageCropUpload
     * @return Response
     */
    public function subirFoto(Request $request, Product $producto, ImageCropUpload $imageCropUpload): Response
    {
        if ($request->isXmlHttpRequest()) {
            if ($request->getMethod() !== 'POST') {
                return new JsonResponse('Método invalido',400);
            }

            if ($producto->getImage()) {
                $imageName = $producto->getImage();
            } else {

                $imageName = strtolower(Slugify::create()->slugify($producto->getImage()));
                $imageName .= uniqid('-',false);
            }

            $imageString = json_decode($request->getContent());

            $entityManager = $this->getDoctrine()->getManager();

            return $imageCropUpload->subirImagen($imageString, $imageName, $producto, $entityManager);
        }

        return $this->render('product/cropper.html.twig', [
            'foto' => $producto->getImage(),
            'nombre' => $producto->getImage(),
            'cancelUrl' => $this->generateUrl('product_show', [ 'id' => $producto->getId() ]),
        ]);
    }

    /**
     * @param Product $product
     * @Route("/public-product/{id}", name="product_public")
     */
    public function publicOnline(Request $request, Product $product)
    {
        if (!$request->isXmlHttpRequest()){
            throw $this->createAccessDeniedException();
        }

        if (!$product->getImage()){
            return new JsonResponse('El producto le falta la imagen', 400);
        }

        $product->getPublic() ? $product->setPublic(0) : $product->setPublic(1);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($product);

        try {
            $entityManager->flush();

            return new JsonResponse(['message' => 'Se ha guardado satisfactoriamente!', 'public' => $product->getPublic()], 200);
        }catch (\Exception $exception){
            return new JsonResponse('Ha ocurrido un error '.$exception->getMessage(), 500);
        }


    }
}
