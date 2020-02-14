<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Region
 *
 * @ORM\Table(name="region")
 * @ORM\Entity(repositoryClass="RegionRepository")
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     * @Assert\Email(
     *      message = "email_error"
     * )
     */
    private $email;

    /**
     * @ORM\OneToMany(targetEntity="PostalCode", mappedBy="region")
     */
    private $postalCodes;
    
    /**
     * Region constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->postalCodes = new ArrayCollection();
    }
    
    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->getName();
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
     * @return Region
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
     * Set dateCreation.
     *
     * @param \DateTime $dateCreation
     *
     * @return Region
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
     * @return Region
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
     * Set userCreation.
     *
     * @param User $userCreation
     *
     * @return Region
     */
    public function setUserCreation(User $userCreation) : self
    {
        $this->userCreation = $userCreation;

        return $this;
    }

    /**
     * Get userCreation.
     *
     * @return User
     */
    public function getUserCreation() : User
    {
        return $this->userCreation;
    }

    /**
     * Set userUpdate.
     *
     * @param User|null $userUpdate
     *
     * @return Region
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
     * Add postalCode.
     *
     * @param PostalCode $postalCode
     *
     * @return Region
     */
    public function addPostalCode(PostalCode $postalCode) : self
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
     * Set deleted.
     *
     * @param \DateTime|null $deleted
     *
     * @return Region
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
     * Set email.
     *
     * @param string|null $email
     *
     * @return Region
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
}
