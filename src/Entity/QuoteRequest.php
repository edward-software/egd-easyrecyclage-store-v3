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
 * QuoteRequest
 *
 * @ORM\Table(name="quoteRequests")
 * @ORM\Entity(repositoryClass="App\Repository\QuoteRequestRepository")
 * @UniqueEntity("number")
 */
class QuoteRequest
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
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_creation_id", referencedColumnName="id", nullable=true)
     */
    private $userCreation;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_update_id", referencedColumnName="id", nullable=true)
     */
    private $userUpdate;

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=255)
     */
    private $locale;

    /**
     * @var string
     *
     * @ORM\Column(name="number", type="string", length=255, nullable=true)
     */
    private $number;

    /**
     * @var string
     *
     * @ORM\Column(name="canton", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"public"})
     */
    private $canton;

    /**
     * @var string
     *
     * @ORM\Column(name="business_name", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"public"})
     */
    private $businessName;
    
    /**
     * @var string
     *
     * @ORM\Column(name="civility", type="string", length=10, nullable=true)
     * @Assert\NotBlank(groups={"public"})
     */
    private $civility;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"public"})
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"public"})
     */
    private $firstName;
    
    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"public"})
     * @Assert\Email(
     *     groups={"public"},
     *      message = "email_error"
     * )
     */
    private $email;
    
    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"public"})
     * @Assert\Regex(
     *     groups={"public"},
     *     pattern="/^\+?(?:[0-9]){6,14}[0-9]$/",
     *     match=true,
     * )
     */
    private $phone;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_multisite", type="boolean")
     * @Assert\NotBlank(groups={"public"})
     */
    private $isMultisite;

    /**
     * @var string
     *
     * @ORM\Column(name="staff", type="text")
     * @Assert\NotBlank(groups={"public"})
     */
    private $staff;

    /**
     * @var string
     *
     * @ORM\Column(name="access", type="text")
     * @Assert\NotBlank(groups={"public"})
     */
    private $access;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="text", nullable=true)
     * @Assert\NotBlank(groups={"public_multisite"})
     */
    private $address;
    
    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true, nullable=true)
     * @Assert\NotBlank(groups={"public_multisite"})
     */
    private $city;
    
    /**
     * @var string

     * "Commentaire client" rempli par l'utilisateur Front Office
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;
    
    /**
     * @var string
     *
     * @ORM\Column(name="quote_status", type="string", length=255)
     */
    private $quoteStatus;
    
    /**
     * @var int
     *
     * @ORM\Column(name="total_amount", type="integer", nullable=true)
     */
    private $totalAmount;

    /**
     * @var int
     *
     * @ORM\Column(name="overall_discount", type="integer")
     */
    private $overallDiscount;

    /**
     * @var string
     *
     * "Commentaire client" rempli par le commercial dans le back-office
     *
     * @ORM\Column(name="salesman_comment", type="text", nullable=true)
     */
    private $salesmanComment;

    /**
     * @var int
     *
     * @ORM\Column(name="annual_budget", type="integer", nullable=true)
     */
    private $annualBudget;

    /**
     * @var string
     *
     * @ORM\Column(name="frequency", type="string", length=255, nullable=true)
     */
    private $frequency;

    /**
     * @var string
     *
     * @ORM\Column(name="frequency_times", type="string", length=255, nullable=true)
     */
    private $frequencyTimes;

    /**
     * @var string
     *
     * @ORM\Column(name="frequency_interval", type="string", length=255, nullable=true)
     */
    private $frequencyInterval;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_id", type="string", length=255, nullable=true)
     */
    private $customerId;

    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", length=255, nullable=true)
     */
    private $reference;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="quoteRequests")
     */
    private $userInCharge;

    /**
     * @var PostalCode
     *
     * @ORM\ManyToOne(targetEntity="PostalCode", inversedBy="quoteRequests")
     */
    private $postalCode;
    
    /**
     * @var QuoteRequestLine[]
     *
     * @ORM\OneToMany(targetEntity="QuoteRequestLine", mappedBy="quoteRequest")
     */
    private $quoteRequestLines;
    
    
    /**
     * QuoteRequest constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->dateCreation = new DateTime();
        $this->quoteRequestLines = new ArrayCollection();
        $this->overallDiscount = 0;
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
     * @return QuoteRequest
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
     * @return QuoteRequest
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
     * @return QuoteRequest
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
     * @param string|null $canton
     *
     * @return QuoteRequest
     */
    public function setCanton($canton = null): self
    {
        $this->canton = $canton;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCanton(): ?string
    {
        return $this->canton;
    }

    /**
     * @param string|null $businessName
     *
     * @return QuoteRequest
     */
    public function setBusinessName($businessName = null) : self
    {
        $this->businessName = $businessName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBusinessName() : ?string
    {
        return $this->businessName;
    }

    /**
     * @param string|null $civility
     *
     * @return QuoteRequest
     */
    public function setCivility($civility = null) : self
    {
        $this->civility = $civility;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCivility() : ?string
    {
        return $this->civility;
    }

    /**
     * @param string|null $lastName
     *
     * @return QuoteRequest
     */
    public function setLastName($lastName = null) : self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastName() : ?string
    {
        return $this->lastName;
    }

    /**
     * @param string|null $firstName
     *
     * @return QuoteRequest
     */
    public function setFirstName($firstName = null) : self
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstName() : ?string
    {
        return $this->firstName;
    }

    /**
     * @param string|null $email
     *
     * @return QuoteRequest
     */
    public function setEmail($email = null) : self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail() : ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $phone
     *
     * @return QuoteRequest
     */
    public function setPhone($phone = null) : self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhone() : ?string
    {
        return $this->phone;
    }

    /**
     * @param bool $isMultisite
     *
     * @return QuoteRequest
     */
    public function setIsMultisite($isMultisite) : self
    {
        $this->isMultisite = $isMultisite;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsMultisite()
    {
        return $this->isMultisite;
    }

    /**
     * @param string|null $address
     *
     * @return QuoteRequest
     */
    public function setAddress($address = null) : self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddress() : ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $city
     *
     * @return QuoteRequest
     */
    public function setCity($city = null) : self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCity() : ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $comment
     *
     * @return QuoteRequest
     */
    public function setComment($comment = null) : self
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getComment() : ?string
    {
        return $this->comment;
    }

    /**
     * @param string $quoteStatus
     *
     * @return QuoteRequest
     */
    public function setQuoteStatus($quoteStatus) : self
    {
        $this->quoteStatus = $quoteStatus;

        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getQuoteStatus() : ?string
    {
        return $this->quoteStatus;
    }

    /**
     * @param int|null $overallDiscount
     *
     * @return QuoteRequest
     */
    public function setOverallDiscount($overallDiscount = null) : self
    {
        $this->overallDiscount = $overallDiscount;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getOverallDiscount() : ?int
    {
        return $this->overallDiscount;
    }

    /**
     * @param string|null $salesmanComment
     *
     * @return QuoteRequest
     */
    public function setSalesmanComment($salesmanComment = null) : self
    {
        $this->salesmanComment = $salesmanComment;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSalesmanComment() : ?string
    {
        return $this->salesmanComment;
    }

    /**
     * @param string|null $frequency
     *
     * @return QuoteRequest
     */
    public function setFrequency($frequency = null) : self
    {
        $this->frequency = $frequency;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFrequency(): ?string
    {
        return $this->frequency;
    }

    /**
     * @param User|null $userCreation
     *
     * @return QuoteRequest
     */
    public function setUserCreation(User $userCreation = null) : self
    {
        $this->userCreation = $userCreation;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUserCreation() : ?User
    {
        return $this->userCreation;
    }

    /**
     * @param User|null $userUpdate
     *
     * @return QuoteRequest
     */
    public function setUserUpdate(User $userUpdate = null) : self
    {
        $this->userUpdate = $userUpdate;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUserUpdate() : ?User
    {
        return $this->userUpdate;
    }

    /**
     * @param User|null $userInCharge
     *
     * @return QuoteRequest
     */
    public function setUserInCharge(User $userInCharge = null) : self
    {
        $this->userInCharge = $userInCharge;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUserInCharge() : ?User
    {
        return $this->userInCharge;
    }

    /**
     * @param QuoteRequestLine $quoteRequestLine
     *
     * @return QuoteRequest
     */
    public function addQuoteRequestLine(QuoteRequestLine $quoteRequestLine) : self
    {
        $this->quoteRequestLines[] = $quoteRequestLine;

        return $this;
    }

    /**
     * @param QuoteRequestLine $quoteRequestLine
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeQuoteRequestLine(QuoteRequestLine $quoteRequestLine) : bool
    {
        return $this->quoteRequestLines->removeElement($quoteRequestLine);
    }

    /**
     * @return Collection
     */
    public function getQuoteRequestLines() : Collection
    {
        return $this->quoteRequestLines;
    }

    /**
     * @param int|null $totalAmount
     *
     * @return QuoteRequest
     */
    public function setTotalAmount($totalAmount = null) : self
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTotalAmount() : ?int
    {
        return $this->totalAmount;
    }

    /**
     * @param string|null $frequencyTimes
     *
     * @return QuoteRequest
     */
    public function setFrequencyTimes($frequencyTimes = null) : self
    {
        $this->frequencyTimes = $frequencyTimes;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFrequencyTimes() : ?string
    {
        return $this->frequencyTimes;
    }

    /**
     * @param string|null $frequencyInterval
     *
     * @return QuoteRequest
     */
    public function setFrequencyInterval($frequencyInterval = null) : self
    {
        $this->frequencyInterval = $frequencyInterval;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFrequencyInterval() : ?string
    {
        return $this->frequencyInterval;
    }

    /**
     * @param string|null $locale
     *
     * @return QuoteRequest
     */
    public function setLocale($locale = null) : self
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocale() : ?string
    {
        return $this->locale;
    }

    /**
     * @param string $staff
     *
     * @return QuoteRequest
     */
    public function setStaff($staff) : self
    {
        $this->staff = $staff;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStaff() : ?string
    {
        return $this->staff;
    }

    /**
     * @param string $access
     *
     * @return QuoteRequest
     */
    public function setAccess($access) : self
    {
        $this->access = $access;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAccess() : ?string
    {
        return $this->access;
    }

    /**
     * @param PostalCode|null $postalCode
     *
     * @return QuoteRequest
     */
    public function setPostalCode(PostalCode $postalCode = null) : self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @return PostalCode|null
     */
    public function getPostalCode() : ?PostalCode
    {
        return $this->postalCode;
    }

    /**
     * @param string|null $number
     *
     * @return QuoteRequest
     */
    public function setNumber($number = null) : self
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNumber() : ?string
    {
        return $this->number;
    }

    /**
     * @param string|null $customerId
     *
     * @return QuoteRequest
     */
    public function setCustomerId($customerId = null) : self
    {
        $this->customerId = $customerId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCustomerId() : ?string
    {
        return $this->customerId;
    }

    /**
     * @param int|null $annualBudget
     *
     * @return QuoteRequest
     */
    public function setAnnualBudget($annualBudget = null) : self
    {
        $this->annualBudget = $annualBudget;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getAnnualBudget() : ?int
    {
        return $this->annualBudget;
    }

    /**
     * @param string|null $reference
     *
     * @return QuoteRequest
     */
    public function setReference($reference = null) : self
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getReference() : ?string
    {
        return $this->reference;
    }
}
