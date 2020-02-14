<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Product
 *
 * @ORM\Table(name="products")
 * @ORM\Entity(repositoryClass="ProductRepository")
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
     * Le volume du produit
     * @ORM\Column(name="capacity", type="string", length=10)
     * @Assert\NotBlank()
     */
    private $capacity;

    /**
     * @var string
     * L'unité du volume du produit (litre, m²,..)
     * @ORM\Column(name="capacityUnit", type="string", length=255)
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
     * @ORM\Column(name="isEnabled", type="boolean")
     * @Assert\NotBlank()
     */
    private $isEnabled;

    /**
     * @var int
     *
     * @ORM\Column(name="setUpPrice", type="integer")
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
     * @ORM\Column(name="rentalUnitPrice", type="integer", nullable=true)
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
     * @ORM\Column(name="transportUnitPrice", type="integer", nullable=true)
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
     * @ORM\Column(name="treatmentUnitPrice", type="integer", nullable=true)
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
     * @ORM\Column(name="traceabilityUnitPrice", type="integer", nullable=true)
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
     * @ORM\OneToMany(targetEntity="Picture", mappedBy="product", cascade={"all"})
     */
    private $pictures;

    /**
     * @ORM\OneToMany(targetEntity="ProductLabel", mappedBy="product", cascade={"all"})
     */
    private $productLabels;
    
    
    /**
     * Product constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->pictures = new ArrayCollection();
        $this->productLabels = new ArrayCollection();
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
     * Set dimensions.
     *
     * @param string $dimensions
     *
     * @return Product
     */
    public function setDimensions($dimensions) : self
    {
        $this->dimensions = $dimensions;

        return $this;
    }

    /**
     * Get dimensions.
     *
     * @return string
     */
    public function getDimensions() : string
    {
        return $this->dimensions;
    }

    /**
     * Set capacityUnit.
     *
     * @param string $capacityUnit
     *
     * @return Product
     */
    public function setCapacityUnit($capacityUnit) : self
    {
        $this->capacityUnit = $capacityUnit;

        return $this;
    }

    /**
     * Get capacityUnit.
     *
     * @return string
     */
    public function getCapacityUnit() : string
    {
        return $this->capacityUnit;
    }

    /**
     * Set dateCreation.
     *
     * @param \DateTime $dateCreation
     *
     * @return Product
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
     * @return Product
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
     * @return Product
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
     * Set IsEnabled.
     *
     * @param bool IsEnabled
     *
     * @return Product
     */
    public function setIsEnabled($isEnabled) : self
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    /**
     * Get isEnabled.
     *
     * @return bool
     */
    public function getIsEnabled() : bool
    {
        return $this->isEnabled;
    }

    /**
     * Set userCreation
     *
     * @param User $userCreation
     *
     * @return Product
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
     * @return Product
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
     * Add picture.
     *
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
     * Remove picture.
     *
     * @param Picture $picture
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removePicture(Picture $picture) : bool
    {
        return $this->pictures->removeElement($picture);
    }

    /**
     * Get pictures.
     *
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
            if ($picture->getType() == 'PILOTPICTURE') {
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
            if ($picture->getType() == 'PICTO') {
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
            if ($picture->getType() == 'PICTURE') {
                $pictures[] = $picture;
            }
        }
        return $pictures;
    }


    /**
     * Add productLabel.
     *
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
     * Remove productLabel.
     *
     * @param ProductLabel $productLabel
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProductLabel(ProductLabel $productLabel) : bool
    {
        return $this->productLabels->removeElement($productLabel);
    }

    /**
     * Get productLabels.
     *
     * @return Collection
     */
    public function getProductLabels() : Collection
    {
        return $this->productLabels;
    }


    /**
     * Set capacity.
     *
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
     * Get capacity.
     *
     * @return string
     */
    public function getCapacity() : string
    {
        return $this->capacity;
    }

    /**
     * Set rentalUnitPrice.
     *
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
     * Get rentalUnitPrice.
     *
     * @return int|null
     */
    public function getRentalUnitPrice() : ?int
    {
        return $this->rentalUnitPrice;
    }

    /**
     * Set transportUnitPrice.
     *
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
     * Get transportUnitPrice.
     *
     * @return int|null
     */
    public function getTransportUnitPrice() : ?int
    {
        return $this->transportUnitPrice;
    }

    /**
     * Set treatmentUnitPrice.
     *
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
     * Get treatmentUnitPrice.
     *
     * @return int|null
     */
    public function getTreatmentUnitPrice() : ?int
    {
        return $this->treatmentUnitPrice;
    }

    /**
     * Set traceabilityUnitPrice.
     *
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
     * Get traceabilityUnitPrice.
     *
     * @return int|null
     */
    public function getTraceabilityUnitPrice() : ?int
    {
        return $this->traceabilityUnitPrice;
    }

    /**
     * Set position.
     *
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
     * Get position.
     *
     * @return int
     */
    public function getPosition() : int
    {
        return $this->position;
    }


    /**
     * Set folderNumber.
     *
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
     * Get folderNumber.
     *
     * @return string
     */
    public function getFolderNumber() : string
    {
        return $this->folderNumber;
    }

    /**
     * Set setUpPrice.
     *
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
     * Get setUpPrice.
     *
     * @return int
     */
    public function getSetUpPrice() : int
    {
        return $this->setUpPrice;
    }
}
