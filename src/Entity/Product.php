<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={
 *          "get" = {
 *              "path" = "/productos",
 *              "normalizationContext"={"groups"={"producto:read"}}
 *          }
 *     },
 *     itemOperations={
 *          "get" = {
 *              "path" = "/producto/{id}"
 *          }
 *     },
 *     paginationItemsPerPage=10
 * )
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 * @UniqueEntity(fields={"url"})
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank()
     * @Groups("producto:read")
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=CategoryProduct::class, inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank()
     * @Groups("producto:read")
     */
    private $categoryProduct;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     */
    private $userApp;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="product")
     */
    private $comments;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Groups("producto:read")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=150, unique=true)
     * @Assert\NotBlank()
     * @Assert\Url()
     * @Groups("producto:read")
     */
    private $url;

    /**
     * @ORM\Column(type="float")
     * @Assert\NotBlank()
     * @Groups("producto:read")
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     * @Groups("producto:read")
     */
    private $image;

    /**
     * @ORM\Column(type="boolean")
     * @Groups("producto:read")
     */
    private $public;

    public function __toString(): ?string
    {
        return (string)$this->name;
    }

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->public = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCategoryProduct(): ?CategoryProduct
    {
        return $this->categoryProduct;
    }

    public function setCategoryProduct(?CategoryProduct $categoryProduct): self
    {
        $this->categoryProduct = $categoryProduct;

        return $this;
    }

    public function getUserApp(): ?User
    {
        return $this->userApp;
    }

    public function setUserApp(?User $userApp): self
    {
        $this->userApp = $userApp;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setProduct($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getProduct() === $this) {
                $comment->setProduct(null);
            }
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * @param mixed $public
     */
    public function setPublic($public): void
    {
        $this->public = $public;
    }

    public function count()
    {
        return $this->comments->count();
    }
}
