<?php
declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
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
     * @ORM\Column(name="dateCreation", type="datetime")
     */
    private $dateCreation;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="dateUpdate", type="datetime", nullable=true)
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
     * @ORM\JoinColumn(name="userCreationId", referencedColumnName="id", nullable=true)
     */
    private $userCreation;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="userUpdateId", referencedColumnName="id", nullable=true)
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
     * @ORM\Column(name="businessName", type="string", length=255, nullable=true)
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
     * @ORM\Column(name="lastName", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"public"})
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="firstName", type="string", length=255, nullable=true)
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
     * @ORM\Column(name="isMultisite", type="boolean")
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
     * @ORM\Column(name="quoteStatus", type="string", length=255)
     */
    private $quoteStatus;
    
    /**
     * @var int
     *
     * @ORM\Column(name="totalAmount", type="integer", nullable=true)
     */
    private $totalAmount;

    /**
     * @var int
     *
     * @ORM\Column(name="overallDiscount", type="integer")
     */
    private $overallDiscount;

    /**
     * @var string
     *
     * "Commentaire client" rempli par le commercial dans le back-office
     *
     * @ORM\Column(name="salesmanComment", type="text", nullable=true)
     */
    private $salesmanComment;

    /**
     * @var int
     *
     * @ORM\Column(name="annualBudget", type="integer", nullable=true)
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
     * @ORM\Column(name="frequencyTimes", type="string", length=255, nullable=true)
     */
    private $frequencyTimes;

    /**
     * @var string
     *
     * @ORM\Column(name="frequencyInterval", type="string", length=255, nullable=true)
     */
    private $frequencyInterval;

    /**
     * @var string
     *
     * @ORM\Column(name="customerId", type="string", length=255, nullable=true)
     */
    private $customerId;

    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", length=255, nullable=true)
     */
    private $reference;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="quoteRequests")
     */
    private $userInCharge;

    /**
     * @ORM\ManyToOne(targetEntity="PostalCode", inversedBy="quoteRequests")
     */
    private $postalCode;
    
    /**
     * @ORM\OneToMany(targetEntity="QuoteRequestLine", mappedBy="quoteRequest")
     */
    private $quoteRequestLines;
    
    
    /**
     * QuoteRequest constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->dateCreation = new DateTime();
        $this->quoteRequestLines = new \Doctrine\Common\Collections\ArrayCollection();
        $this->overallDiscount = 0;
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
     * Set dateCreation.
     *
     * @param DateTime $dateCreation
     *
     * @return QuoteRequest
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
     * @return QuoteRequest
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
     * @return QuoteRequest
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
     * Set canton.
     *
     * @param string|null $canton
     *
     * @return QuoteRequest
     */
    public function setCanton($canton = null) : self
    {
        $this->canton = $canton;

        return $this;
    }

    /**
     * Get canton.
     *
     * @return string|null
     */
    public function getCanton(): ?string
    {
        return $this->canton;
    }

    /**
     * Set businessName.
     *
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
     * Get businessName.
     *
     * @return string|null
     */
    public function getBusinessName() : ?string
    {
        return $this->businessName;
    }

    /**
     * Set civility.
     *
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
     * Get civility.
     *
     * @return string|null
     */
    public function getCivility() : ?string
    {
        return $this->civility;
    }

    /**
     * Set lastName.
     *
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
     * Get lastName.
     *
     * @return string|null
     */
    public function getLastName() : ?string
    {
        return $this->lastName;
    }

    /**
     * Set firstName.
     *
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
     * Get firstName.
     *
     * @return string|null
     */
    public function getFirstName() : ?string
    {
        return $this->firstName;
    }

    /**
     * Set email.
     *
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
     * Get email.
     *
     * @return string|null
     */
    public function getEmail() : ?string
    {
        return $this->email;
    }

    /**
     * Set phone.
     *
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
     * Get phone.
     *
     * @return string|null
     */
    public function getPhone() : ?string
    {
        return $this->phone;
    }

    /**
     * Set isMultisite.
     *
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
     * Get isMultisite.
     *
     * @return bool|null
     */
    public function getIsMultisite() : ?bool
    {
        return $this->isMultisite;
    }

    /**
     * Set address.
     *
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
     * Get address.
     *
     * @return string|null
     */
    public function getAddress() : ?string
    {
        return $this->address;
    }

    /**
     * Set city.
     *
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
     * Get city.
     *
     * @return string|null
     */
    public function getCity() : ?string
    {
        return $this->city;
    }

    /**
     * Set comment.
     *
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
     * Get comment.
     *
     * @return string|null
     */
    public function getComment() : ?string
    {
        return $this->comment;
    }

    /**
     * Set quoteStatus.
     *
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
     * Get quoteStatus.
     *
     * @return string
     */
    public function getQuoteStatus() : string
    {
        return $this->quoteStatus;
    }

    /**
     * Set overallDiscount.
     *
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
     * Get overallDiscount.
     *
     * @return int|null
     */
    public function getOverallDiscount() : ?int
    {
        return $this->overallDiscount;
    }

    /**
     * Set salesmanComment.
     *
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
     * Get salesmanComment.
     *
     * @return string|null
     */
    public function getSalesmanComment() : ?string
    {
        return $this->salesmanComment;
    }

    /**
     * Set frequency.
     *
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
     * Get frequency.
     *
     * @return string|null
     */
    public function getFrequency(): ?string
    {
        return $this->frequency;
    }

    /**
     * Set userCreation.
     *
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
     * Get userCreation.
     *
     * @return User|null
     */
    public function getUserCreation() : ?User
    {
        return $this->userCreation;
    }

    /**
     * Set userUpdate.
     *
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
     * Get userUpdate.
     *
     * @return User|null
     */
    public function getUserUpdate() : ?User
    {
        return $this->userUpdate;
    }

    /**
     * Set userInCharge.
     *
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
     * Get userInCharge.
     *
     * @return User|null
     */
    public function getUserInCharge() : ?User
    {
        return $this->userInCharge;
    }

    /**
     * Add quoteRequestLine.
     *
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
     * Remove quoteRequestLine.
     *
     * @param QuoteRequestLine $quoteRequestLine
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeQuoteRequestLine(QuoteRequestLine $quoteRequestLine) : bool
    {
        return $this->quoteRequestLines->removeElement($quoteRequestLine);
    }

    /**
     * Get quoteRequestLines.
     *
     * @return Collection
     */
    public function getQuoteRequestLines() : Collection
    {
        return $this->quoteRequestLines;
    }

    /**
     * Set totalAmount.
     *
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
     * Get totalAmount.
     *
     * @return int|null
     */
    public function getTotalAmount() : ?int
    {
        return $this->totalAmount;
    }

    /**
     * Set frequencyTimes.
     *
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
     * Get frequencyTimes.
     *
     * @return string|null
     */
    public function getFrequencyTimes() : ?string
    {
        return $this->frequencyTimes;
    }

    /**
     * Set frequencyInterval.
     *
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
     * Get frequencyInterval.
     *
     * @return string|null
     */
    public function getFrequencyInterval() : ?string
    {
        return $this->frequencyInterval;
    }

    /**
     * Set locale.
     *
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
     * Get locale.
     *
     * @return string|null
     */
    public function getLocale() : ?string
    {
        return $this->locale;
    }

    /**
     * Set staff.
     *
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
     * Get staff.
     *
     * @return string|null
     */
    public function getStaff() : ?string
    {
        return $this->staff;
    }

    /**
     * Set access.
     *
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
     * Get access.
     *
     * @return string|null
     */
    public function getAccess() : ?string
    {
        return $this->access;
    }

    /**
     * Set postalCode.
     *
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
     * Get postalCode.
     *
     * @return PostalCode|null
     */
    public function getPostalCode() : ?PostalCode
    {
        return $this->postalCode;
    }

    /**
     * Set number.
     *
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
     * Get number.
     *
     * @return string|null
     */
    public function getNumber() : ?string
    {
        return $this->number;
    }

    /**
     * Set customerId.
     *
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
     * Get customerId.
     *
     * @return string|null
     */
    public function getCustomerId() : ?string
    {
        return $this->customerId;
    }

    /**
     * Set annualBudget.
     *
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
     * Get annualBudget.
     *
     * @return int|null
     */
    public function getAnnualBudget() : ?int
    {
        return $this->annualBudget;
    }

    /**
     * Set reference.
     *
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
     * Get reference.
     *
     * @return string|null
     */
    public function getReference() : ?string
    {
        return $this->reference;
    }
}
