<?php

namespace App\Controller;

use App\Entity\Config;
use App\Form\ConfigType;
use App\Repository\ConfigRepository;
use App\Service\FileUploader;
use App\Service\FileUploaderLogo;
use Psr\Http\Message\UploadedFileInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Image;

/**
 * @Route("/cpanel/config")
 */
class ConfigController extends AbstractController
{
    /**
     * @Route("/", name="config_index", methods={"GET"})
     */
    public function index(ConfigRepository $configRepository): Response
    {
        return $this->render('config/index.html.twig', [
            'configs' => $configRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="config_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $config = new Config();

        $form = $this->createForm(ConfigType::class, $config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($config);
            $entityManager->flush();

            $this->addFlash('success', 'Se ha guardado satisfactoriamente');

            return $this->redirectToRoute('config_index');
        }

        return $this->render('config/new.html.twig', [
            'config' => $config,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="config_show", methods={"GET"})
     */
    public function show(Config $config): Response
    {
        return $this->render('config/show.html.twig', [
            'config' => $config,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="config_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Config $config): Response
    {
        $form = $this->createForm(ConfigType::class, $config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Se ha guardado satisfactoriamente');

            return $this->redirectToRoute('config_index');
        }

        return $this->render('config/edit.html.twig', [
            'config' => $config,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="config_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Config $config): Response
    {
        if ($this->isCsrfTokenValid('delete'.$config->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($config);
            $entityManager->flush();
        }

        return $this->redirectToRoute('config_index');
    }

    /**
     * @param Request $request
     * @param UploadedFile $uploadedFile
     * @param ContainerInterface $container
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Route("/upload/logo/", name="config_logo")
     */
    public function uploadLogo(Request $request, FileUploaderLogo $uploadedFile, ContainerInterface $container)
    {
        $form = $this->createFormBuilder()
            ->add('logo', FileType::class, [
                'attr' => [
                    'accept' => 'image/png'
                ],
                'constraints' => new Image(
                    [
                        'mimeTypes' => 'image/png',
                        'mimeTypesMessage' => 'Debe ser png'
                    ]
                ),
            ])->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $file = $form['logo']->getData();

            if ($file){
                $uploadedFile->upload($file);
            }

            $this->addFlash('success', 'Se ha subido la imagen satisfactoriamente!');

            return $this->redirectToRoute('config_index');
        }

        return $this->render('config/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
