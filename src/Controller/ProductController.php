<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductViewImage;
use App\Form\ProductEditType;
use App\Form\ProductType;
use App\Form\ProductViewImageType;
use App\Repository\ProductRepository;
use App\Repository\ProductViewImageRepository;
use App\Service\FileUploader;
use App\Service\ImageCropUpload;
use Cocur\Slugify\Slugify;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
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

            return $this->redirectToRoute('producto_cropper', [ 'id' => $product->getId() ]);
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
        $form = $this->createForm(ProductEditType::class, $product);
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

    /**
     * @param Product $product
     * @Route("/view/{id}", name="producto_view_images_new")
     */
    public function viewImagesProduct(Request $request, Product $product, FileUploader $fileUploader, ProductViewImageRepository $productViewImageRepository)
    {
        $productViewImage = new ProductViewImage();

        $form = $this->createForm(ProductViewImageType::class, $productViewImage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $image = $form['image']->getData();
            if ($image) {
                $filename = $fileUploader->upload($image);
                $productViewImage->setImage($filename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $productViewImage->setProduct($product);
            $entityManager->persist($productViewImage);
            $entityManager->flush();

            $this->addFlash('success', 'Se ha subido la imagen satisfactoriamente');

            return $this->redirectToRoute('producto_view_images_new', [
                'id' => $product->getId()
            ]);
        }

        return $this->render('product/subir_vistas.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
            'views' => $productViewImageRepository->findBy(['product' => $product])
        ]);
    }

    /**
     * @param ProductViewImage $productViewImage
     * @Route("/eliminar/{id}", name="producto_view_images_eliminar")
     */
    public function eliminarView(ProductViewImage $productViewImage, ContainerInterface $container)
    {
        $productId = $productViewImage->getProduct()->getId();
        $image = $productViewImage->getImage();
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($productViewImage);
        $entityManager->flush();

        try {
            unlink($container->getParameter('app.images').$image);
        }catch (\Exception $exception){

        }
        $this->addFlash('success', 'Se ha eliminado satisfactoriamente');

        return $this->redirectToRoute('producto_view_images_new', [
            'id' => $productId
        ]);
    }

    /**
     * @param Request $request
     * @param Product $product
     * @Route("/{id}/eliminar/", name="product_eliminar")
     */
    public function eliminar(Request $request, Product $product, ContainerInterface $container)
    {
        if (!$request->isXmlHttpRequest()){
            throw $this->createNotFoundException();
        }
        $imageSource = $container->getParameter('app.images').$product->getImage();

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($product);

        try {
            $entityManager->flush();
            unlink($imageSource);

            return new JsonResponse('Se ha elimiando satisfactoriamente', 200);
        }catch (\Exception $exception){
            return new JsonResponse('Ha ocurrido un error '.$exception->getMessage(), 400);
        }
    }

    /**
     * @param ProductRepository $productRepository
     * @Route("/reset/counter/", name="product_reset_contadores")
     */
    public function resetContadores(ProductRepository $productRepository)
    {
        $entityManager = $this->getDoctrine()->getManager();

        foreach ($productRepository->findResetCounter() as $item) {
            /**
             * @var Product $item
             */
            $item->setLikeCount(0)
                ->setVisita(0);
            $entityManager->persist($item);
        }

        try {
            $entityManager->flush();

            return new JsonResponse(['Se ha reseteado satisfactoriamente el contador de visita del producto y los like'], 200);
        }catch (\Exception $exception){
            return new JsonResponse('Ha ocurrido un error '.$exception->getMessage());
        }
    }
}
