<?php
declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PostalCode
 *
 * @ORM\Table(name="postalCodes")
 * @ORM\Entity(repositoryClass="App\Repository\PostalCodeRepository")
 * @UniqueEntity(fields={"code"}, repositoryMethod="isCodeUnique")
 */
class PostalCode
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
     * @var DateTime
     *
     * @ORM\Column(name="deleted", type="datetime", nullable=true)
     */
    private $deleted;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string")
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^\d{2}(\*|(?:\d{2}))$/",
     *     match=true,
     *     message="Le codes postal doivent être un nombre de 4 caractères ou 2 suivis d'une *. (ex: 15*, 1530)"
     * )
     */
    private $code;

    /**
     * @var float
     *
     * @ORM\Column(name="set_up_rate", type="bigint")
     * @Assert\NotBlank()
     */
    private $setUpRate;

    /**
     * @var float
     *
     * @ORM\Column(name="rental_rate", type="bigint")
     * @Assert\NotBlank()
     */
    private $rentalRate;

    /**
     * @var float
     *
     * @ORM\Column(name="transport_rate", type="bigint")
     * @Assert\NotBlank()
     */
    private $transportRate;

    /**
     * @var float
     *
     * @ORM\Column(name="treatment_rate", type="bigint")
     * @Assert\NotBlank()
     */
    private $treatmentRate;
    
    /**
     * @var float
     *
     * @ORM\Column(name="traceability_rate", type="bigint")
     * @Assert\NotBlank()
     */
    private $traceabilityRate;
    
    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string")
     * @Assert\NotBlank()
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="zone", type="string")
     * @Assert\NotBlank()
     */
    private $zone;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="postalCodes")
     */
    private $userInCharge;

    /**
     * @var Region
     *
     * @ORM\ManyToOne(targetEntity="Region", inversedBy="postalCodes")
     */
    private $region;

    /**
     * @var QuoteRequest[]
     *
     * @ORM\OneToMany(targetEntity="QuoteRequest", mappedBy="postal_code")
     */
    private $quoteRequests;
    
    
    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->dateCreation = new DateTime();
        $this->quoteRequests = new ArrayCollection();
    }

    
    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param DateTime $dateCreation
     *
     * @return PostalCode
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
     * @return PostalCode
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
     * @return PostalCode
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
     * @param string $code
     *
     * @return PostalCode
     */
    public function setCode($code): self
    {
        $this->code = $code;

        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param int $transportRate
     *
     * @return PostalCode
     */
    public function setTransportRate($transportRate): self
    {
        $this->transportRate = $transportRate;

        return $this;
    }
    
    /**
     * @return float|null
     */
    public function getTransportRate(): ?float
    {
        return (float)$this->transportRate;
    }

    /**
     * @param int $treatmentRate
     *
     * @return PostalCode
     */
    public function setTreatmentRate($treatmentRate): self
    {
        $this->treatmentRate = $treatmentRate;

        return $this;
    }
    
    /**
     * @return float|null
     */
    public function getTreatmentRate(): ?float
    {
        return (float)$this->treatmentRate;
    }

    /**
     * @param int $traceabilityRate
     *
     * @return PostalCode
     */
    public function setTraceabilityRate($traceabilityRate): self
    {
        $this->traceabilityRate = $traceabilityRate;

        return $this;
    }
    
    /**
     * @return float|null
     */
    public function getTraceabilityRate(): ?float
    {
        return (float)$this->traceabilityRate;
    }

    /**
     * @param User $userCreation
     *
     * @return PostalCode
     */
    public function setUserCreation(User $userCreation): self
    {
        $this->userCreation = $userCreation;

        return $this;
    }

    /**
     * @return User
     */
    public function getUserCreation(): User
    {
        return $this->userCreation;
    }

    /**
     * @param User|null $userUpdate
     *
     * @return PostalCode
     */
    public function setUserUpdate(User $userUpdate = null): self
    {
        $this->userUpdate = $userUpdate;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUserUpdate(): ?User
    {
        return $this->userUpdate;
    }

    /**
     * @param User|null $userInCharge
     *
     * @return PostalCode
     */
    public function setUserInCharge(User $userInCharge = null): self
    {
        $this->userInCharge = $userInCharge;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUserInCharge(): ?User
    {
        return $this->userInCharge;
    }

    /**
     * @param Region|null $region
     *
     * @return PostalCode
     */
    public function setRegion(Region $region = null): self
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return Region|null
     */
    public function getRegion(): ?Region
    {
        return $this->region;
    }

    /**
     * @param string $city
     *
     * @return PostalCode
     */
    public function setCity($city): self
    {
        $this->city = $city;

        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }
    
    /**
     * @param $zone
     *
     * @return $this
     */
    public function setZone($zone): self
    {
        $this->zone = $zone;

        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getZone(): ?string
    {
        return $this->zone;
    }

    /**
     * @param QuoteRequest $quoteRequest
     *
     * @return PostalCode
     */
    public function addQuoteRequest(QuoteRequest $quoteRequest): self
    {
        $this->quoteRequests[] = $quoteRequest;

        return $this;
    }

    /**
     * @param QuoteRequest $quoteRequest
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeQuoteRequest(QuoteRequest $quoteRequest): bool
    {
        return $this->quoteRequests->removeElement($quoteRequest);
    }

    /**
     * @return Collection
     */
    public function getQuoteRequests(): Collection
    {
        return $this->quoteRequests;
    }

    /**
     * @param int $setUpRate
     *
     * @return PostalCode
     */
    public function setSetUpRate($setUpRate): self
    {
        $this->setUpRate = $setUpRate;

        return $this;
    }
    
    /**
     * @return float|null
     */
    public function getSetUpRate(): ?float
    {
        return (float)$this->setUpRate;
    }

    /**
     * @param int $rentalRate
     *
     * @return PostalCode
     */
    public function setRentalRate($rentalRate): self
    {
        $this->rentalRate = $rentalRate;

        return $this;
    }
    
    /**
     * @return float|null
     */
    public function getRentalRate(): ?float
    {
        return (float)$this->rentalRate;
    }
}
