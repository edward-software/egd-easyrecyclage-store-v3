<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Picture
 *
 * @ORM\Table(name="pictures")
 * @ORM\Entity(repositoryClass="PictureRepository")
 */
class Picture
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\Product", inversedBy="pictures")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\CustomArea", inversedBy="pictures")
     */
    private $customArea;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * Set path.
     *
     * @param string $path
     *
     * @return Picture
     */
    public function setPath($path) : self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path.
     *
     * @return string|null
     */
    public function getPath() : ?string
    {
        return $this->path;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return Picture
     */
    public function setType($type) : self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * Set product.
     *
     * @param Product|null $product
     *
     * @return Picture
     */
    public function setProduct(Product $product = null) : self
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product.
     *
     * @return Product|null
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set customArea.
     *
     * @param CustomArea|null $customArea
     *
     * @return Picture
     */
    public function setCustomArea(CustomArea $customArea = null)
    {
        $this->customArea = $customArea;

        return $this;
    }

    /**
     * Get customArea.
     *
     * @return CustomArea|null
     */
    public function getCustomArea()
    {
        return $this->customArea;
    }
}
