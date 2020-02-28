<?php
declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Product
 *
 * @ORM\Table(name="products")
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 */
class Product
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
     * Le volume du produit
     * @ORM\Column(name="capacity", type="string", length=10)
     * @Assert\NotBlank()
     */
    private $capacity;

    /**
     * @var string
     * L'unité du volume du produit (litre, m²,..)
     * @ORM\Column(name="capacity_unit", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $capacityUnit;

    /**
     * @var string
     *
     * @ORM\Column(name="dimensions", type="string", length=500)
     * @Assert\NotBlank()
     */
    private $dimensions;

    /**
     * @var string
     * Le nombre de documents accepté
     * @ORM\Column(name="folder_number", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $folderNumber;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="is_enabled", type="boolean")
     * @Assert\NotBlank()
     */
    private $isEnabled;

    /**
     * @var int
     *
     * @ORM\Column(name="set_up_price", type="integer")
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^\d{1,6}((\.|\,)\d{1,2})?$/",
     *     match=true,
     *     message="la valeur doit être un nombre entre 0 et 999 999,99 ('.' autorisé)"
     * )
     */
    private $setUpPrice;

    /**
     * @var int
     *
     * @ORM\Column(name="rental_unit_price", type="integer", nullable=true)
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^\d{1,6}((\.|\,)\d{1,2})?$/",
     *     match=true,
     *     message="la valeur doit être un nombre entre 0 et 999 999,99 ('.' autorisé)"
     * )
     */
    private $rentalUnitPrice;

    /**
     * @var int
     *
     * @ORM\Column(name="transport_unit_price", type="integer", nullable=true)
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^\d{1,6}((\.|\,)\d{1,2})?$/",
     *     match=true,
     *     message="la valeur doit être un nombre entre 0 et 999 999,99 ('.' autorisé)"
     * )
     */
    private $transportUnitPrice;

    /**
     * @var int
     *
     * @ORM\Column(name="treatment_unit_price", type="integer", nullable=true)
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^\d{1,6}((\.|\,)\d{1,2})?$/",
     *     match=true,
     *     message="la valeur doit être un nombre entre 0 et 999 999,99 ('.' autorisé)"
     * )
     */
    private $treatmentUnitPrice;

    /**
     * @var int
     *
     * @ORM\Column(name="traceability_unit_price", type="integer", nullable=true)
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^\d{1,6}((\.|\,)\d{1,2})?$/",
     *     match=true,
     *     message="la valeur doit être un nombre entre 0 et 999 999,99 ('.' autorisé)"
     * )
     */
    private $traceabilityUnitPrice;

    /**
     * @var int
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="position", type="integer")
     */
    private $position;

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
     * @var Picture[]
     *
     * @ORM\OneToMany(targetEntity="Picture", mappedBy="product", cascade={"all"})
     */
    private $pictures;

    /**
     * @var ProductLabel[]
     *
     * @ORM\OneToMany(targetEntity="ProductLabel", mappedBy="product", cascade={"all"})
     */
    private $productLabels;
    
    
    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->dateCreation = new DateTime();
        $this->pictures = new ArrayCollection();
        $this->productLabels = new ArrayCollection();
    }

    
    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string $dimensions
     *
     * @return Product
     */
    public function setDimensions($dimensions): self
    {
        $this->dimensions = $dimensions;

        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getDimensions(): ?string
    {
        return $this->dimensions;
    }

    /**
     * @param string $capacityUnit
     *
     * @return Product
     */
    public function setCapacityUnit($capacityUnit): self
    {
        $this->capacityUnit = $capacityUnit;

        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getCapacityUnit(): ?string
    {
        return $this->capacityUnit;
    }

    /**
     * @param DateTime $dateCreation
     *
     * @return Product
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
     * @return Product
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
     * @return Product
     */
    public function setDeleted($deleted = null): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getDeleted(): ?DateTime
    {
        return $this->deleted;
    }

    /**
     * @param bool IsEnabled
     *
     * @return Product
     */
    public function setIsEnabled($isEnabled): self
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }
    
    /**
     * @return bool|null
     */
    public function getIsEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    /**
     * @param User $userCreation
     *
     * @return Product
     */
    public function setUserCreation(User $userCreation): self
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
     * @return Product
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
     * @param Picture $picture
     *
     * @return Product
     */
    public function addPicture(Picture $picture) : self
    {
        $this->pictures[] = $picture;

        return $this;
    }

    /**
     * @param Picture $picture
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removePicture(Picture $picture) : bool
    {
        return $this->pictures->removeElement($picture);
    }

    /**
     * @return Collection
     */
    public function getPictures() : Collection
    {
        return $this->pictures;
    }
    
    /**
     * @return array
     */
    public function getPilotPictures() : array
    {
        $pilotPictures = [];
        foreach ($this->pictures as $picture) {
            if ($picture->getType() === 'PILOTPICTURE') {
                $pilotPictures[] = $picture;
            }
        }
        return $pilotPictures;
    }
    
    /**
     * @return array
     */
    public function getPictos() : array
    {
        $pictos = [];
        foreach ($this->pictures as $picture) {
            if ($picture->getType() === 'PICTO') {
                $pictos[] = $picture;
            }
        }
        return $pictos;
    }
    
    /**
     * @return array
     */
    public function getPicturesPictures()
    {
        $pictures = [];
        foreach ($this->pictures as $picture) {
            if ($picture->getType() === 'PICTURE') {
                $pictures[] = $picture;
            }
        }
        return $pictures;
    }


    /**
     * @param ProductLabel $productLabel
     *
     * @return Product
     */
    public function addProductLabel(ProductLabel $productLabel) : self
    {
        $this->productLabels[] = $productLabel;

        return $this;
    }

    /**
     * @param ProductLabel $productLabel
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProductLabel(ProductLabel $productLabel) : bool
    {
        return $this->productLabels->removeElement($productLabel);
    }

    /**
     * @return Collection
     */
    public function getProductLabels() : Collection
    {
        return $this->productLabels;
    }


    /**
     * @param string $capacity
     *
     * @return Product
     */
    public function setCapacity($capacity) : self
    {
        $this->capacity = $capacity;

        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getCapacity() : ?string
    {
        return $this->capacity;
    }

    /**
     * @param int|null $rentalUnitPrice
     *
     * @return Product
     */
    public function setRentalUnitPrice($rentalUnitPrice = null) : self
    {
        $this->rentalUnitPrice = $rentalUnitPrice;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getRentalUnitPrice() : ?int
    {
        return $this->rentalUnitPrice;
    }

    /**
     * @param int|null $transportUnitPrice
     *
     * @return Product
     */
    public function setTransportUnitPrice($transportUnitPrice = null) : self
    {
        $this->transportUnitPrice = $transportUnitPrice;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTransportUnitPrice() : ?int
    {
        return $this->transportUnitPrice;
    }

    /**
     * @param int|null $treatmentUnitPrice
     *
     * @return Product
     */
    public function setTreatmentUnitPrice($treatmentUnitPrice = null) : self
    {
        $this->treatmentUnitPrice = $treatmentUnitPrice;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTreatmentUnitPrice() : ?int
    {
        return $this->treatmentUnitPrice;
    }

    /**
     * @param int|null $traceabilityUnitPrice
     *
     * @return Product
     */
    public function setTraceabilityUnitPrice($traceabilityUnitPrice = null) : self
    {
        $this->traceabilityUnitPrice = $traceabilityUnitPrice;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTraceabilityUnitPrice() : ?int
    {
        return $this->traceabilityUnitPrice;
    }

    /**
     * @param int $position
     *
     * @return Product
     */
    public function setPosition($position) : self
    {
        $this->position = $position;

        return $this;
    }
    
    /**
     * @return int|null
     */
    public function getPosition() : ?int
    {
        return $this->position;
    }


    /**
     * @param string $folderNumber
     *
     * @return Product
     */
    public function setFolderNumber($folderNumber) : self
    {
        $this->folderNumber = $folderNumber;

        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getFolderNumber() : ?string
    {
        return $this->folderNumber;
    }

    /**
     * @param int $setUpPrice
     *
     * @return Product
     */
    public function setSetUpPrice($setUpPrice) : self
    {
        $this->setUpPrice = $setUpPrice;

        return $this;
    }
    
    /**
     * @return int|null
     */
    public function getSetUpPrice() : ?int
    {
        return $this->setUpPrice;
    }
}
