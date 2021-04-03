<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Form\TagType;
use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/cpanel/tag")
 */
class TagController extends AbstractController
{
    /**
     * @Route("/", name="tag_index", methods={"GET"})
     */
    public function index(TagRepository $tagRepository): Response
    {
        return $this->render('tag/index.html.twig', [
            'tags' => $tagRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="tag_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($tag);
            $entityManager->flush();

            $this->addFlash('success', 'Se ha guardado satisfactoriamente');

            return $this->redirectToRoute('tag_index');
        }

        return $this->render('tag/new.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="tag_show", methods={"GET"})
     */
    public function show(Tag $tag): Response
    {
        return $this->render('tag/show.html.twig', [
            'tag' => $tag,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="tag_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Tag $tag): Response
    {
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Se ha guardado satisfactoriamente');

            return $this->redirectToRoute('tag_index');
        }

        return $this->render('tag/edit.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="tag_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Tag $tag): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tag->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($tag);
            $entityManager->flush();
        }

        return $this->redirectToRoute('tag_index');
    }

    /**
     * @param Tag $tag
     * @Route("/tag/active/{id}", name="tag_active")
     */
    public function activeTag(Request $request, Tag $tag)
    {
        if (!$request->isXmlHttpRequest()){
            throw $this->createNotFoundException();
        }

        $tag->getActive() ? $tag->setActive(0) : $tag->setActive(1);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($tag);

        $entityManager->flush();

        return new JsonResponse(['massage' => 'Se ha cambiado el estado de la etiqueta satisfactoriamente', 'public' => $tag->getActive()], 200);
    }

    /**
     * @param Request $request
     * @param TagRepository $tagRepository
     * @return JsonResponse
     * @Route("/json/list/", name="tag_json_list")
     */
    public function jsonListTagActive(Request $request, TagRepository $tagRepository)
    {
//        if (!$request->isXmlHttpRequest()){
//            throw $this->createNotFoundException();
//        }

        $tags = $tagRepository->findBy(['active' => true]);
        $list = [];

        foreach ($tags as $tag){
            $list [] = ['id' => $tag->getId(), 'text' => $tag->getName()];
        }

        return new JsonResponse($list, 200);
    }
}
