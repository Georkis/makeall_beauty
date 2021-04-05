<?php

namespace App\EventListener;


use App\Entity\Config;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\UserProvider\UserProviderFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Twig\Environment;

class MttoListener
{
    /**
     * @var Environment
     */
    private $template;

    /**
     * @var EntityManagerInterface
     */
    private $entityManeger;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(EntityManagerInterface $entityManager, Environment $template, RouterInterface $router, TokenStorageInterface $tokenStorage, ContainerInterface $container)
    {
        $this->entityManeger = $entityManager;
        $this->template = $template;
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->container = $container;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $debug = in_array($this->container->get('kernel')->getEnvironment(), array('test', 'dev'));
        if ($debug){
            return;
        }
        $mtto = @$this->entityManeger->getRepository(Config::class)->findOneBy(['nombre' => 'app-mtto'])->getValor() ?? 0;
        $routerLogin = $this->router->match('/login')['_route'];
        $routerCurrent = trim($event->getRequest()->get('_route'));

        $user = $this->tokenStorage->getToken();
        $engine = $this->template;

        if ($routerLogin != $routerCurrent && $mtto == 1 and $user->getUsername() === "anon."){
            $content = $engine->render('mtto/mtto.html.twig');
            $event->setResponse(new Response($content), 503);
            $event->stopPropagation();
        }
    }
}