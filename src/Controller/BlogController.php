<?php

namespace App\Controller;

use App\Entity\Blog;
use App\Form\BlogType;
use App\Repository\BlogRepository;
use App\Service\ImageCropUpload;
use Cocur\Slugify\Slugify;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/cpanel/blog")
 */
class BlogController extends AbstractController
{
    /**
     * @Route("/", name="blog_index", methods={"GET"})
     */
    public function index(BlogRepository $blogRepository): Response
    {
        return $this->render('blog/index.html.twig', [
            'blogs' => $blogRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="blog_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $blog = new Blog();
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($blog);
            $entityManager->flush();

            $this->addFlash('success', 'Se ha guardado satisfactoriamente');

            return $this->redirectToRoute('blog_cropper', [
                'id' => $blog->getId()
            ]);
        }

        return $this->render('blog/new.html.twig', [
            'blog' => $blog,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="blog_show", methods={"GET"})
     */
    public function show(Blog $blog): Response
    {
        return $this->render('blog/show.html.twig', [
            'blog' => $blog,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="blog_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Blog $blog): Response
    {
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Se ha guardado satisfactoriamente');

            return $this->redirectToRoute('blog_index');
        }

        return $this->render('blog/edit.html.twig', [
            'blog' => $blog,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="blog_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Blog $blog): Response
    {
        if ($this->isCsrfTokenValid('delete'.$blog->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($blog);
            $entityManager->flush();
        }

        return $this->redirectToRoute('blog_index');
    }

    /**
     * @Route("/cropper/{id}/", name="blog_cropper", methods={"GET","POST"})
     *
     * @param Request $request
     * @param Blog $blog
     * @param ImageCropUpload $imageCropUpload
     * @return Response
     */
    public function subirFoto(Request $request, Blog $blog, ImageCropUpload $imageCropUpload): Response
    {
        if ($request->isXmlHttpRequest()) {
            if ($request->getMethod() !== 'POST') {
                return new JsonResponse('Método invalido',400);
            }

            if ($blog->getImage()) {
                $imageName = $blog->getImage();
            } else {

                $imageName = strtolower(Slugify::create()->slugify($blog->getImage()));
                $imageName .= uniqid('gsn_',false);
            }

            $imageString = json_decode($request->getContent());

            $entityManager = $this->getDoctrine()->getManager();

            return $imageCropUpload->subirImagen($imageString, $imageName, $blog, $entityManager);
        }

        return $this->render('blog/cropper.html.twig', [
            'foto' => $blog->getImage(),
            'nombre' => $blog->getImage(),
            'cancelUrl' => $this->generateUrl('blog_show', [ 'id' => $blog->getId() ]),
        ]);
    }

    /**
     * @param Request $request
     * @param Blog $blog
     * @Route("/public/{id}", name="blog_public")
     */
    public function publicBlog(Request $request, Blog $blog)
    {
        if (!$request->isXmlHttpRequest()){
            throw $this->createNotFoundException();
        }
        $blog->getPublic() ? $blog->setPublic(0) : $blog->setPublic(1);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($blog);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Se ha cambiado el estado de la publicación satisfactoriamente ('.($blog->getPublic() ? 'Publico' : 'No publico').')',
            'public' => $blog->getPublic()
        ], 200);
    }

    /**
     * @param Request $request
     * @param Blog $blog
     * @param ContainerInterface $container
     * @return JsonResponse
     * @Route("/eliminar/{id}", name="blog_eliminar")
     */
    public function eliminar(Request $request, Blog $blog, ContainerInterface $container)
    {
        if (!$request->isXmlHttpRequest()){
            throw $this->createNotFoundException();
        }
        $image = $blog->getImage();
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($blog);

        try {
            $entityManager->flush();

            unlink($container->getParameter('app.images').$image);

            return new JsonResponse('Se ha eliminado satisfactoriamente!', 200);
        }catch (\Exception $exception){
            return new JsonResponse('Ha ocurrido un error '.$exception->getMessage(), 500);
        }
    }
}
