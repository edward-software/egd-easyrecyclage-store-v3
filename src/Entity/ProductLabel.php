<?php
declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Product
 *
 * @ORM\Table(name="productLabels")
 * @ORM\Entity(repositoryClass="App\Repository\ProductLabelRepository")
 * @UniqueEntity(
 *     fields={"language", "product"},
 *     message="This language is already defined pour this product."
 * )
 *
 */
class ProductLabel
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
     * @var DateTime
     *
     * @ORM\Column(name="date_creation", type="datetime")
     */
    private $dateCreation;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date_update", type="datetime", nullable=true)
     */
    private $dateUpdate;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="deleted", type="datetime", nullable=true)
     */
    private $deleted;
    
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="short_description", type="text")
     * @Assert\NotBlank()
     */
    private $shortDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="product_version", type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $version;

    /**
     * @var string
     *
     * @ORM\Column(name="lock_type", type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $lockType;

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $language;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_creation_id", referencedColumnName="id", nullable=false)
     */
    private $userCreation;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_update_id", referencedColumnName="id", nullable=true)
     */
    private $userUpdate;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="productLabels")
     */
    private $product;
    
    
    /**
     * ProductLabel constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->dateCreation = new DateTime();
    }
    

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string $name
     *
     * @return ProductLabel
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $shortDescription
     *
     * @return ProductLabel
     */
    public function setShortDescription($shortDescription): self
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    /**
     * @param string $language
     *
     * @return ProductLabel
     */
    public function setLanguage($language): self
    {
        $this->language = $language;

        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getLanguage(): ?string
    {
        return $this->language;
    }

    /**
     * @param DateTime $dateCreation
     *
     * @return ProductLabel
     */
    public function setDateCreation($dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateCreation(): DateTime
    {
        return $this->dateCreation;
    }

    /**
     * @param DateTime|null $dateUpdate
     *
     * @return ProductLabel
     */
    public function setDateUpdate($dateUpdate = null): self
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getDateUpdate(): ?DateTime
    {
        return $this->dateUpdate;
    }

    /**
     * @param DateTime|null $deleted
     *
     * @return ProductLabel
     */
    public function setDeleted($deleted = null) : self
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getDeleted() : ?DateTime
    {
        return $this->deleted;
    }

    /**
     * @param User $userCreation
     *
     * @return ProductLabel
     */
    public function setUserCreation(User $userCreation) : self
    {
        $this->userCreation = $userCreation;

        return $this;
    }

    /**
     * @return User
     */
    public function getUserCreation() : User
    {
        return $this->userCreation;
    }

    /**
     * @param User $userUpdate
     *
     * @return ProductLabel
     */
    public function setUserUpdate(User $userUpdate = null) : self
    {
        $this->userUpdate = $userUpdate;

        return $this;
    }

    /**
     * @return User
     */
    public function getUserUpdate() : User
    {
        return $this->userUpdate;
    }

    /**
     * @param Product|null $product
     *
     * @return ProductLabel
     */
    public function setProduct(Product $product = null) : self
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return Product|null
     */
    public function getProduct() : ?Product
    {
        return $this->product;
    }

    /**
     * @param string $version
     *
     * @return ProductLabel
     */
    public function setVersion($version) : self
    {
        $this->version = $version;

        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getVersion() : ?string
    {
        return $this->version;
    }

    /**
     * @param string|null $lockType
     *
     * @return ProductLabel
     */
    public function setLockType($lockType = null) : self
    {
        $this->lockType = $lockType;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLockType() : ?string
    {
        return $this->lockType;
    }
}
