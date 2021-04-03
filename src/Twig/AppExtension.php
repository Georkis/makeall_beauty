<?php
namespace App\Twig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('Truncar', [$this, 'Truncar']),
        ];
    }

    /**
     * 
     * @param type $frase_entrada
     * @param type $cortar
     * @return string
     * @Descrición
     * Trunca un texto según la cantidad que desees cortar... sin necesidad de cortar palabra.
     */
    public function Truncar($frase_entrada,$cortar){
        return strlen($frase_entrada) > $cortar ? (substr($frase_entrada, 0, strrpos(substr($frase_entrada, 0, $cortar), ' ')).'...') : $frase_entrada;
    }


    public function getName()
    {
        return 'utilidades';
    }
}