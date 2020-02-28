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
     * CustomArea constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->dateCreation = new DateTime();
        $this->pictures = new ArrayCollection();
    }
    
    
    /**
     * Get id.
     *
     * @return int
     */
    public function getId():int
    {
        return $this->id;
    }
    
    /**
     * Set dateCreation.
     *
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
     * Get dateCreation.
     *
     * @return DateTime
     */
    public function getDateCreation():DateTime
    {
        return $this->dateCreation;
    }
    
    /**
     * Set dateUpdate.
     *
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
     * Get dateUpdate.
     *
     * @return DateTime|null
     */
    public function getDateUpdate(): ?DateTime
    {
        return $this->dateUpdate;
    }
    
    /**
     * Set leftContent.
     *
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
     * Get leftContent.
     *
     * @return string
     */
    public function getLeftContent(): string
    {
        return $this->leftContent;
    }
    
    /**
     * Set rightContent.
     *
     * @param string $rightContent
     *
     * @return CustomArea
     */
    public function setRightContent($rightContent): self
    {
        $this->rightContent = $rightContent;
        
        return $this;
    }
    
    /**
     * Get rightContent.
     *
     * @return string
     */
    public function getRightContent(): string
    {
        return $this->rightContent;
    }
    
    /**
     * Set code.
     *
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
     * Get code.
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }
    
    /**
     * Set language.
     *
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
     * Get language.
     *
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }
    
    /**
     * Set userCreation.
     *
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
     * Get userCreation.
     *
     * @return User
     */
    public function getUserCreation() : User
    {
        return $this->userCreation;
    }
    
    /**
     * Add picture.
     *
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
    public function getLeftPictures() : array
    {
        $leftPictures = array();
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
        $rightPictures = array();
        foreach ($this->pictures as $picture) {
            if ($picture->getType() === 'RIGHT') {
                $rightPictures[] = $picture;
            }
        }
        return $rightPictures;
    }
    
    /**
     * Set deleted.
     *
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
     * Get deleted.
     *
     * @return DateTime|null
     */
    public function getDeleted() : ?DateTime
    {
        return $this->deleted;
    }
    
    /**
     * Set isDisplayed.
     *
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
     * Get isDisplayed.
     *
     * @return bool
     */
    public function getIsDisplayed() : bool
    {
        return $this->isDisplayed;
    }
    
    /**
     * Set userUpdate.
     *
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
     * Get userUpdate.
     *
     * @return User|null
     */
    public function getUserUpdate() : ?User
    {
        return $this->userUpdate;
    }
}
