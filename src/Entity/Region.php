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
 * Region
 *
 * @ORM\Table(name="region")
 * @ORM\Entity(repositoryClass="App\Repository\RegionRepository")
 */
class Region
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     * @Assert\Email(message = "email_error")
     */
    private $email;

    /**
     * @var PostalCode[]
     *
     * @ORM\OneToMany(targetEntity="PostalCode", mappedBy="region")
     */
    private $postalCodes;
    
    
    /**
     * Region constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->dateCreation = new DateTime();
        $this->postalCodes = new ArrayCollection();
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
     * Set name.
     *
     * @param string $name
     *
     * @return Region
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
     * @param $dateCreation
     *
     * @return $this
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
     * @return Region
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
     * @param User $userCreation
     *
     * @return Region
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
     * @return Region
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
     * @param PostalCode $postalCode
     *
     * @return Region
     */
    public function addPostalCode(PostalCode $postalCode): self
    {
        $this->postalCodes[] = $postalCode;

        return $this;
    }

    /**
     * @param PostalCode $postalCode
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removePostalCode(PostalCode $postalCode): bool
    {
        return $this->postalCodes->removeElement($postalCode);
    }

    /**
     * @return Collection
     */
    public function getPostalCodes(): Collection
    {
        return $this->postalCodes;
    }

    /**
     * @param DateTime|null $deleted
     *
     * @return Region
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
     * @param string|null $email
     *
     * @return Region
     */
    public function setEmail($email = null): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }
    
    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->getName();
    }
}
