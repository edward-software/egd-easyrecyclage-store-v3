<?php
declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Ramsey\Uuid\UuidInterface;

/**
 * Cart
 *
 * @ORM\Table(name="carts")
 * @ORM\Entity(repositoryClass="App\Repository\CartRepository")
 */
class Cart
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date_creation", type="datetime")
     */
    private $dateCreation;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="date_update", type="datetime", nullable=true)
     */
    private $dateUpdate;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="disabled", type="datetime", nullable=true)
     */
    private $disabled;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     */
    private $city;

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
     * @var array|null
     *
     * @ORM\Column(name="content", type="json", nullable=true)
     */
    private $content;
    
    
    /**
     * Cart constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->dateCreation = new DateTime();
        $this->content = [];
    }
    

    /**
     * Get id.
     *
     * @return UuidInterface
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * Set dateCreation.
     *
     * @param DateTime $dateCreation
     *
     * @return Cart
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
     * @return Cart
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
     * Set disabled.
     *
     * @param DateTime|null $disabled
     *
     * @return Cart
     */
    public function setDisabled($disabled = null): self
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * Get disabled.
     *
     * @return DateTime|null
     */
    public function getDisabled(): ?DateTime
    {
        return $this->disabled;
    }

    /**
     * Set city.
     *
     * @param string|null $city
     *
     * @return Cart
     */
    public function setCity($city = null): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city.
     *
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * Set frequency.
     *
     * @param string|null $frequency
     *
     * @return Cart
     */
    public function setFrequency($frequency = null): self
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
     * Set content.
     *
     * @param array|null $content
     *
     * @return Cart
     */
    public function setContent($content = null) : self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return array|null
     */
    public function getContent() : ?array
    {
        return $this->content;
    }

    /**
     * Set frequencyTimes.
     *
     * @param string|null $frequencyTimes
     *
     * @return Cart
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
     * @return Cart
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
}
