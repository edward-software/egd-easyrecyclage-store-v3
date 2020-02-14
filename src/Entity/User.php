<?php
declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, repositoryMethod="isMailUnique")
 * @UniqueEntity(fields={"username"}, repositoryMethod="isUsernameUnique")
 * @UniqueEntity(fields={"usernameCanonical"}, repositoryMethod="isUsernameCanonicalUnique")
 */
class User extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    protected $username;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     * @Assert\NotBlank()
     * @var string
     */
    protected $plainPassword;
    
    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Email(
     *      message = "email_error",
     *      checkMX = true
     * )
     */
    protected $email;

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
     * @var string
     *
     * @ORM\Column(name="companyName", type="string", length=255, nullable=true)
     */
    private $companyName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="firstName", type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lang", type="string", length=255, nullable=true)
     */
    private $lang;

    /**
     * @ORM\OneToMany(targetEntity="PostalCode", mappedBy="userInCharge")
     */
    private $postalCodes;

    /**
     * @ORM\OneToMany(targetEntity="QuoteRequest", mappedBy="userInCharge")
     */
    private $quoteRequests;


    public function __construct()
    {
        parent::__construct();

        $this->dateCreation = new DateTime();
        $this->quoteRequests = new ArrayCollection();
        $this->postalCodes = new ArrayCollection();
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
     * Set companyName.
     *
     * @param string|null $companyName
     *
     * @return User
     */
    public function setCompanyName($companyName = null) : User
    {
        $this->companyName = $companyName;

        return $this;
    }

    /**
     * Get companyName.
     *
     * @return string|null
     */
    public function getCompanyName() : ?string
    {
        return $this->companyName;
    }

    /**
     * Set lastName.
     *
     * @param string|null $lastName
     *
     * @return User
     */
    public function setLastName($lastName = null) : User
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
     * @return User
     */
    public function setFirstName($firstName = null) : User
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
     * Set dateCreation.
     *
     * @param DateTime $dateCreation
     *
     * @return User
     */
    public function setDateCreation($dateCreation) : User
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation.
     *
     * @return DateTime
     */
    public function getDateCreation() : DateTime
    {
        return $this->dateCreation;
    }

    /**
     * Set dateUpdate.
     *
     * @param DateTime|null $dateUpdate
     *
     * @return User
     */
    public function setDateUpdate($dateUpdate = null) : User
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Get dateUpdate.
     *
     * @return DateTime|null
     */
    public function getDateUpdate() : ?DateTime
    {
        return $this->dateUpdate;
    }

    /**
     * Set deleted.
     *
     * @param DateTime|null $deleted
     *
     * @return User
     */
    public function setDeleted($deleted = null) : User
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted.
     *
     * @return DateTime|null
     */
    public function getDeleted() : ?\DateTime
    {
        return $this->deleted;
    }

    /**
     * Add postalCode.
     *
     * @param PostalCode $postalCode
     *
     * @return User
     */
    public function addPostalCode(PostalCode $postalCode) : User
    {
        $this->postalCodes[] = $postalCode;

        return $this;
    }

    /**
     * Remove postalCode.
     *
     * @param PostalCode $postalCode
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removePostalCode(PostalCode $postalCode) : bool
    {
        return $this->postalCodes->removeElement($postalCode);
    }

    /**
     * Get postalCodes.
     *
     * @return Collection
     */
    public function getPostalCodes() : Collection
    {
        return $this->postalCodes;
    }

    /**
     * Add quoteRequest.
     *
     * @param QuoteRequest $quoteRequest
     *
     * @return User
     */
    public function addQuoteRequest(QuoteRequest $quoteRequest) : User
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
     * Set lang.
     *
     * @param string|null $lang
     *
     * @return User
     */
    public function setLang($lang = null) : User
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get lang.
     *
     * @return string|null
     */
    public function getLang() : ?string
    {
        return $this->lang;
    }
}
