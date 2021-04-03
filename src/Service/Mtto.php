<?php


namespace App\Service;

use App\Entity\Config;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class Mtto
{
    private $security;

    private $manager;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->manager = $entityManager;
    }

    public function statusSite()
    {
        $user = $this->security->getUser();
        $mantenimiento = $this->manager->getRepository(Config::class)->findOneBy(['nombre' => 'app-mantenimiento'])->getValor();

        return (int)$mantenimiento && !$user;
    }
}