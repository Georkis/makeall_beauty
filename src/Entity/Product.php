<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ProductRepository;
use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 * @UniqueEntity(fields={"url"})
 * @UniqueEntity(fields={"slug"})
 */
class Product
{
    const page =1;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=CategoryProduct::class, inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank()
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
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=150, unique=true)
     * @Assert\NotBlank()
     * @Assert\Url()
     */
    private $url;

    /**
     * @ORM\Column(type="float")
     * @Assert\NotBlank()
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="boolean")
     */
    private $public;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $visita;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $likeCount;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity=ProductViewImage::class, mappedBy="product")
     */
    private $productViewImages;

    /**
     * @ORM\ManyToMany(targetEntity=Tag::class, inversedBy="products")
     */
    private $tag;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $lead;

    public function __toString(): ?string
    {
        return (string)$this->name;
    }

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->public = false;
        $this->productViewImages = new ArrayCollection();
        $this->tag = new ArrayCollection();
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
        $this->slug = Slugify::create()->slugify($name);

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

    public function getVisita(): ?int
    {
        return $this->visita;
    }

    public function setVisita(?int $visita): self
    {
        $this->visita = $visita;

        return $this;
    }

    public function getLikeCount(): ?int
    {
        return $this->likeCount;
    }

    public function setLikeCount(?int $likeCount): self
    {
        $this->likeCount = $likeCount;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection|ProductViewImage[]
     */
    public function getProductViewImages(): Collection
    {
        return $this->productViewImages;
    }

    public function addProductViewImage(ProductViewImage $productViewImage): self
    {
        if (!$this->productViewImages->contains($productViewImage)) {
            $this->productViewImages[] = $productViewImage;
            $productViewImage->setProduct($this);
        }

        return $this;
    }

    public function removeProductViewImage(ProductViewImage $productViewImage): self
    {
        if ($this->productViewImages->removeElement($productViewImage)) {
            // set the owning side to null (unless already changed)
            if ($productViewImage->getProduct() === $this) {
                $productViewImage->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTag(): Collection
    {
        return $this->tag;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tag->contains($tag)) {
            $this->tag[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tag->removeElement($tag);

        return $this;
    }

    public function getLead(): ?string
    {
        return $this->lead;
    }

    public function setLead(string $lead): self
    {
        $this->lead = $lead;

        return $this;
    }
}
