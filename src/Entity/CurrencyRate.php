<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Debtor
 *
 * @ORM\Table(name="currencyRates")
 * @ORM\Entity()
 */
class CurrencyRate
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="source", type="integer", nullable=false)
     */
    private $source;

    /**
     * @var string
     *
     * @ORM\Column(name="target", type="integer", nullable=false)
     */
    private $target;

    /**
     * @var string
     *
     * @ORM\Column(name="rate", type="decimal", precision=20, scale=10, nullable=true)
     */
    private $rate;
    
    
    /**
     * CurrencyRate constructor.
     */
    public function __construct()
    {

    }
    
    
    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
    
    /**
     * @param $source
     *
     * @return $this
     */
    public function setSource($source): self
    {
        $this->source = $source;

        return $this;
    }
    
    /**
     * @return int
     */
    public function getSource(): int
    {
        return $this->source;
    }
    
    /**
     * @param $target
     *
     * @return $this
     */
    public function setTarget($target): self
    {
        $this->target = $target;

        return $this;
    }
    
    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }
    
    /**
     * @param $rate
     *
     * @return $this
     */
    public function setRate($rate): self
    {
        $this->rate = $rate;

        return $this;
    }
    
    /**
     * @return string
     */
    public function getRate(): string
    {
        return $this->rate;
    }
}
