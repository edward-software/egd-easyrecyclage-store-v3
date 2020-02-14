<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Product
 *
 * @ORM\Table(name="productLabels")
 * @ORM\Entity(repositoryClass="ProductRepository")
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
     * @var \DateTime
     *
     * @ORM\Column(name="dateCreation", type="datetime")
     */
    private $dateCreation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateUpdate", type="datetime", nullable=true)
     */
    private $dateUpdate;

    /**
     * @var \DateTime
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
     * @ORM\Column(name="shortDescription", type="text")
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
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="userCreationId", referencedColumnName="id", nullable=false)
     */
    private $userCreation;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="userUpdateId", referencedColumnName="id", nullable=true)
     */
    private $userUpdate;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="productLabels")
     */
    private $product;

    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dateCreation = new \DateTime();
    }
    

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
     * Set name.
     *
     * @param string $name
     *
     * @return ProductLabel
     */
    public function setName($name) : self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Set shortDescription.
     *
     * @param string $shortDescription
     *
     * @return ProductLabel
     */
    public function setShortDescription($shortDescription) : self
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    /**
     * Get shortDescription.
     *
     * @return string
     */
    public function getShortDescription() : string
    {
        return $this->shortDescription;
    }

    /**
     * Set language.
     *
     * @param string $language
     *
     * @return ProductLabel
     */
    public function setLanguage($language) : self
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language.
     *
     * @return string
     */
    public function getLanguage() : string
    {
        return $this->language;
    }

    /**
     * Set dateCreation.
     *
     * @param \DateTime $dateCreation
     *
     * @return ProductLabel
     */
    public function setDateCreation($dateCreation) : self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation.
     *
     * @return \DateTime
     */
    public function getDateCreation() : \DateTime
    {
        return $this->dateCreation;
    }

    /**
     * Set dateUpdate.
     *
     * @param \DateTime|null $dateUpdate
     *
     * @return ProductLabel
     */
    public function setDateUpdate($dateUpdate = null) : self
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Get dateUpdate.
     *
     * @return \DateTime|null
     */
    public function getDateUpdate() : ?\DateTime
    {
        return $this->dateUpdate;
    }

    /**
     * Set deleted.
     *
     * @param \DateTime|null $deleted
     *
     * @return ProductLabel
     */
    public function setDeleted($deleted = null) : self
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted.
     *
     * @return \DateTime|null
     */
    public function getDeleted() : ?\DateTime
    {
        return $this->deleted;
    }

    /**
     * Set userCreation
     *
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
     * Get userCreation
     *
     * @return User
     */
    public function getUserCreation() : User
    {
        return $this->userCreation;
    }

    /**
     * Set userUpdate
     *
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
     * Get userUpdate
     *
     * @return User
     */
    public function getUserUpdate() : User
    {
        return $this->userUpdate;
    }

    /**
     * Set product.
     *
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
     * Get product.
     *
     * @return Product|null
     */
    public function getProduct() : ?Product
    {
        return $this->product;
    }

    /**
     * Set version.
     *
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
     * Get version.
     *
     * @return string
     */
    public function getVersion() : string
    {
        return $this->version;
    }

    /**
     * Set lockType.
     *
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
     * Get lockType.
     *
     * @return string|null
     */
    public function getLockType() : ?string
    {
        return $this->lockType;
    }
}
