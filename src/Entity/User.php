<?php
declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Exception;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, repositoryMethod="isMailUnique")
 * @UniqueEntity(fields={"username"}, repositoryMethod="isUsernameUnique")
 *
 * @package App\Entity
 */
class User implements UserInterface
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
     * @ORM\Column(name="username", type="string", length=180, unique=true)
     * @Assert\NotBlank()
     */
    private $username;
    
    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100, unique=true)
     *
     * @Assert\NotBlank()
     * @Assert\Email(message = "email_error")
     */
    private $email;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled;
    
    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", nullable=true)
     */
    private $salt;
    
    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string")
     */
    private $password;
    
    /**
     * @var string
     *getDateUpdate
     * @ORM\Column(name="last_login", type="string", nullable=true)
     */
    private $lastLogin;
    
    /**
     * @var string
     *
     * @ORM\Column(name="confirmation_token", type="string", length=180, nullable=true)
     */
    private $confirmationToken;
    
    /**
     * @var DateTime
     *
     * @ORM\Column(name="password_requested_at", type="datetime", nullable=true)
     */
    private $passwordRequestedAt;
    
    /**
     * @var array
     *
     * @ORM\Column(name="roles", type="json")
     */
    private $roles = [];
    
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
     * @ORM\Column(name="company_name", type="string", length=255, nullable=true)
     */
    private $companyName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lang", type="string", length=255, nullable=true)
     */
    private $lang;

    /**
     * @var PostalCode[]
     *
     * @ORM\OneToMany(targetEntity="PostalCode", mappedBy="userInCharge")
     */
    private $postalCodes;

    /**
     * @var QuoteRequest[]
     *
     * @ORM\OneToMany(targetEntity="QuoteRequest", mappedBy="userInCharge")
     */
    private $quoteRequests;
    
    
    /**
     * User constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->dateCreation = new DateTime();
        $this->quoteRequests = new ArrayCollection();
        $this->postalCodes = new PersistentCollection;
    }
    
    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
    
    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    
    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }
    
    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }
    
    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }
    
    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
    
    /**
     * @return bool|null
     */
    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }
    
    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }
    
    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }
    
    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
    
    /**
     * @return string
     */
    public function getLastLogin(): string
    {
        return $this->lastLogin;
    }
    
    /**
     * @param string $lastLogin
     */
    public function setLastLogin(string $lastLogin): void
    {
        $this->lastLogin = $lastLogin;
    }
    
    /**
     * @return string
     */
    public function getConfirmationToken(): string
    {
        return $this->confirmationToken;
    }
    
    /**
     * @param string $confirmationToken
     */
    public function setConfirmationToken(string $confirmationToken): void
    {
        $this->confirmationToken = $confirmationToken;
    }
    
    /**
     * @return DateTime
     */
    public function getPasswordRequestedAt(): DateTime
    {
        return $this->passwordRequestedAt;
    }
    
    /**
     * @param DateTime $passwordRequestedAt
     */
    public function setPasswordRequestedAt(DateTime $passwordRequestedAt): void
    {
        $this->passwordRequestedAt = $passwordRequestedAt;
    }
    
    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }
    
    /**
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }
    
    /**
     * @return DateTime
     */
    public function getDateCreation(): DateTime
    {
        return $this->dateCreation;
    }
    
    /**
     * @param DateTime $dateCreation
     */
    public function setDateCreation(DateTime $dateCreation): void
    {
        $this->dateCreation = $dateCreation;
    }
    
    /**
     * @return DateTime|null
     */
    public function getDateUpdate(): ?DateTime
    {
        return $this->dateUpdate;
    }
    
    /**
     * @param DateTime $dateUpdate
     */
    public function setDateUpdate(DateTime $dateUpdate): void
    {
        $this->dateUpdate = $dateUpdate;
    }
    
    /**
     * @return DateTime|null
     */
    public function getDeleted(): ?DateTime
    {
        return $this->deleted;
    }
    
    /**
     * @param DateTime $deleted
     */
    public function setDeleted(DateTime $deleted): void
    {
        $this->deleted = $deleted;
    }
    
    /**
     * @return string|null
     */
    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }
    
    /**
     * @param string $companyName
     */
    public function setCompanyName(string $companyName): void
    {
        $this->companyName = $companyName;
    }
    
    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }
    
    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }
    
    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }
    
    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }
    
    /**
     * @return string|null
     */
    public function getLang(): ?string
    {
        return $this->lang;
    }
    
    /**
     * @param string $lang
     */
    public function setLang(string $lang): void
    {
        $this->lang = $lang;
    }
    
    /**
     * @return PersistentCollection
     */
    public function getPostalCodes(): PersistentCollection
    {
        return $this->postalCodes;
    }
    
    /**
     * @param ArrayCollection $postalCodes
     */
    public function setPostalCodes(ArrayCollection $postalCodes): void
    {
        $this->postalCodes = $postalCodes;
    }
    
    /**
     * @return ArrayCollection
     */
    public function getQuoteRequests(): ArrayCollection
    {
        return $this->quoteRequests;
    }
    
    /**
     * @param ArrayCollection $quoteRequests
     */
    public function setQuoteRequests(ArrayCollection $quoteRequests): void
    {
        $this->quoteRequests = $quoteRequests;
    }
    
    /**
     * @return string|void|null
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }
    
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->password = null;
    }
}
