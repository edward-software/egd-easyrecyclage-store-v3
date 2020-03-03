<?php
declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CustomArea
 *
 * @ORM\Table(name="custom_area")
 * @ORM\Entity(repositoryClass="App\Repository\CustomAreaRepository")
 */
class CustomArea
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
     * @ORM\JoinColumn(name="user_creation_id", referencedColumnName="id", nullable=false)
     */
    private $userCreation;
    
    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_update_id", referencedColumnName="id", nullable=true)
     */
    private $userUpdate;
    
    /**
     * @var bool
     *
     * @ORM\Column(name="is_displayed", type="boolean")
     * @Assert\NotBlank()
     */
    private $isDisplayed;
    
    /**
     * @var string
     *
     * @ORM\Column(name="left_content", type="text")
     * @Assert\NotNull()
     */
    private $leftContent;
    
    /**
     * @var string
     *
     * @ORM\Column(name="right_content", type="text")
     * @Assert\NotNull()
     */
    private $rightContent;
    
    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=50)
     * @Assert\NotNull()
     */
    private $code;
    
    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $language;
    
    /**
     * @var Picture[]
     *
     * @ORM\OneToMany(targetEntity="Picture", mappedBy="customArea", cascade={"all"})
     */
    private $pictures;
    
    
    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->dateCreation = new DateTime();
        $this->pictures = new ArrayCollection();
    }
    
    
    /**
     * @return int
     */
    public function getId():int
    {
        return $this->id;
    }
    
    /**
     * @param DateTime $dateCreation
     *
     * @return CustomArea
     */
    public function setDateCreation($dateCreation):self
    {
        $this->dateCreation = $dateCreation;
        
        return $this;
    }
    
    /**
     * @return DateTime
     */
    public function getDateCreation():DateTime
    {
        return $this->dateCreation;
    }
    
    /**
     * @param DateTime|null $dateUpdate
     *
     * @return CustomArea
     */
    public function setDateUpdate($dateUpdate = null):self
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
     * @param string $leftContent
     *
     * @return CustomArea
     */
    public function setLeftContent($leftContent): self
    {
        $this->leftContent = $leftContent;
        
        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getLeftContent(): ?string
    {
        return $this->leftContent;
    }
    
    /**
     * @param $rightContent
     *
     * @return $this
     */
    public function setRightContent($rightContent): self
    {
        $this->rightContent = $rightContent;
        
        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getRightContent(): ?string
    {
        return $this->rightContent;
    }
    
    /**
     * @param string $code
     *
     * @return CustomArea
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
     * @param string $language
     *
     * @return CustomArea
     */
    public function setLanguage($language): self
    {
        $this->language = $language;
        
        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getLanguage(): ?string
    {
        return $this->language;
    }
    
    /**
     * @param User $userCreation
     *
     * @return CustomArea
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
     * @param Picture $picture
     *
     * @return CustomArea
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
    public function getLeftPictures() : array
    {
        $leftPictures = [];
        foreach ($this->pictures as $picture) {
            if ($picture->getType() === 'LEFT') {
                $leftPictures[] = $picture;
            }
        }
        
        return $leftPictures;
    }
    
    /**
     * @return array
     */
    public function getRightPictures() : array
    {
        $rightPictures = [];
        foreach ($this->pictures as $picture) {
            if ($picture->getType() === 'RIGHT') {
                $rightPictures[] = $picture;
            }
        }
        return $rightPictures;
    }
    
    /**
     * @param DateTime|null $deleted
     *
     * @return CustomArea
     */
    public function setDeleted($deleted = null) : self
    {
        $this->deleted = $deleted;
        
        return $this;
    }
    
    /**
     * @return DateTime|null
     */
    public function getDeleted() : ?DateTime
    {
        return $this->deleted;
    }
    
    /**
     * @param bool $isDisplayed
     *
     * @return CustomArea
     */
    public function setIsDisplayed($isDisplayed) : self
    {
        $this->isDisplayed = $isDisplayed;
        
        return $this;
    }
    
    /**
     * @return bool|null
     */
    public function getIsDisplayed() : ?bool
    {
        return $this->isDisplayed;
    }
    
    /**
     * @param User|null $userUpdate
     *
     * @return CustomArea
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
}
