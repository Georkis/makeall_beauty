<?php

namespace App\Entity;

use App\Repository\VisitaPaisRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VisitaPaisRepository::class)
 */
class VisitaPais
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $pais;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $tcpIp;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=4)
     */
    private $ioc;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $gec;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $continente;

    /**
     * @ORM\Column(type="integer")
     */
    private $cantidad;

    public function __construct()
    {
        $this->date = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPais(): ?string
    {
        return $this->pais;
    }

    public function setPais(string $pais): self
    {
        $this->pais = $pais;

        return $this;
    }

    public function getTcpIp(): ?string
    {
        return $this->tcpIp;
    }

    public function setTcpIp(string $tcpIp): self
    {
        $this->tcpIp = $tcpIp;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getIoc(): ?string
    {
        return $this->ioc;
    }

    public function setIoc(string $ioc): self
    {
        $this->ioc = $ioc;

        return $this;
    }

    public function getGec(): ?string
    {
        return $this->gec;
    }

    public function setGec(string $gec): self
    {
        $this->gec = $gec;

        return $this;
    }

    public function getContinente(): ?string
    {
        return $this->continente;
    }

    public function setContinente(string $continente): self
    {
        $this->continente = $continente;

        return $this;
    }

    public function getCantidad(): ?int
    {
        return $this->cantidad;
    }

    public function setCantidad(int $cantidad): self
    {
        $this->cantidad = $cantidad;

        return $this;
    }
}
