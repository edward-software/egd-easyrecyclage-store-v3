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
     * @var int
     *
     * @ORM\Column(name="set_up_rate", type="bigint")
     * @Assert\NotBlank()
     */
    private $setUpRate;

    /**
     * @var int
     *
     * @ORM\Column(name="rental_rate", type="bigint")
     * @Assert\NotBlank()
     */
    private $rentalRate;

    /**
     * @var int
     *
     * @ORM\Column(name="transport_rate", type="bigint")
     * @Assert\NotBlank()
     */
    private $transportRate;

    /**
     * @var int
     *
     * @ORM\Column(name="treatment_rate", type="bigint")
     * @Assert\NotBlank()
     */
    private $treatmentRate;
    
    /**
     * @var int
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
     * @ORM\OneToMany(targetEntity="QuoteRequest", mappedBy="postalCode")
     */
    private $quoteRequests;
    
    
    /**
     * PostalCode constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->dateCreation = new DateTime();
        $this->quoteRequests = new ArrayCollection();
    }

    
    /**
     * Get id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set dateCreation.
     *
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
     * Get dateCreation.
     *
     * @return DateTime
     */
    public function getDateCreation(): DateTime
    {
        return $this->dateCreation;
    }

    /**
     * Set dateUpdate.
     *
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
     * Get dateUpdate.
     *
     * @return DateTime|null
     */
    public function getDateUpdate(): ?DateTime
    {
        return $this->dateUpdate;
    }

    /**
     * Set deleted.
     *
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
     * Get deleted.
     *
     * @return DateTime|null
     */
    public function getDeleted(): ?DateTime
    {
        return $this->deleted;
    }

    /**
     * Set code.
     *
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
     * Get code.
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Set transportRate.
     *
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
     * Get transportRate.
     *
     * @return int
     */
    public function getTransportRate(): int
    {
        return $this->transportRate;
    }

    /**
     * Set treatmentRate.
     *
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
     * Get treatmentRate.
     *
     * @return int
     */
    public function getTreatmentRate(): int
    {
        return $this->treatmentRate;
    }

    /**
     * Set traceabilityRate.
     *
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
     * Get traceabilityRate.
     *
     * @return int
     */
    public function getTraceabilityRate(): int
    {
        return $this->traceabilityRate;
    }

    /**
     * Set userCreation.
     *
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
     * Get userCreation.
     *
     * @return User
     */
    public function getUserCreation(): User
    {
        return $this->userCreation;
    }

    /**
     * Set userUpdate.
     *
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
     * Get userUpdate.
     *
     * @return User|null
     */
    public function getUserUpdate(): ?User
    {
        return $this->userUpdate;
    }

    /**
     * Set userInCharge.
     *
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
     * Get userInCharge.
     *
     * @return User|null
     */
    public function getUserInCharge(): ?User
    {
        return $this->userInCharge;
    }

    /**
     * Set region.
     *
     * @param Region|null $region
     *
     * @return PostalCode
     */
    public function setRegion(Region $region = null) : self
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region.
     *
     * @return Region|null
     */
    public function getRegion() : ?Region
    {
        return $this->region;
    }

    /**
     * Set city.
     *
     * @param string $city
     *
     * @return PostalCode
     */
    public function setCity($city) : self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city.
     *
     * @return string
     */
    public function getCity() : string
    {
        return $this->city;
    }

    /**
     * Set zone.
     *
     * @param string $zone
     *
     * @return PostalCode
     */
    public function setZone($zone) : self
    {
        $this->zone = $zone;

        return $this;
    }

    /**
     * Get zone.
     *
     * @return string
     */
    public function getZone() : string
    {
        return $this->zone;
    }

    /**
     * Add quoteRequest.
     *
     * @param QuoteRequest $quoteRequest
     *
     * @return PostalCode
     */
    public function addQuoteRequest(QuoteRequest $quoteRequest) : self
    {
        $this->quoteRequests[] = $quoteRequest;

        return $this;
    }

    /**
     * Remove quoteRequest.
     *
     * @param QuoteRequest $quoteRequest
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeQuoteRequest(QuoteRequest $quoteRequest) : bool
    {
        return $this->quoteRequests->removeElement($quoteRequest);
    }

    /**
     * Get quoteRequests.
     *
     * @return Collection
     */
    public function getQuoteRequests() : Collection
    {
        return $this->quoteRequests;
    }

    /**
     * Set setUpRate.
     *
     * @param int $setUpRate
     *
     * @return PostalCode
     */
    public function setSetUpRate($setUpRate) : self
    {
        $this->setUpRate = $setUpRate;

        return $this;
    }

    /**
     * Get setUpRate.
     *
     * @return int
     */
    public function getSetUpRate() : int
    {
        return $this->setUpRate;
    }

    /**
     * Set rentalRate.
     *
     * @param int $rentalRate
     *
     * @return PostalCode
     */
    public function setRentalRate($rentalRate) : self
    {
        $this->rentalRate = $rentalRate;

        return $this;
    }

    /**
     * Get rentalRate.
     *
     * @return int
     */
    public function getRentalRate() : int
    {
        return $this->rentalRate;
    }
}
