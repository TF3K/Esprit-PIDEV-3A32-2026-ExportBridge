<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ProductCategoryRepository;

#[ORM\Entity(repositoryClass: ProductCategoryRepository::class)]
#[ORM\Table(name: 'product_categories')]
class ProductCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $name = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $slug = null;

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'productCategory')]
    private Collection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        if (!$this->products instanceof Collection) {
            $this->products = new ArrayCollection();
        }
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->getProducts()->contains($product)) {
            $this->getProducts()->add($product);
        }
        return $this;
    }

    public function removeProduct(Product $product): self
    {
        $this->getProducts()->removeElement($product);
        return $this;
    }

}
