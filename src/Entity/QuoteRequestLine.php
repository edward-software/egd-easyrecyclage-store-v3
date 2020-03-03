<?php
declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * QuoteRequestLine
 *
 * @ORM\Table(name="quoteRequestLines")
 * @ORM\Entity(repositoryClass="App\Repository\QuoteRequestLineRepository")
 */
class QuoteRequestLine
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
     * @var string
     *
     * @ORM\Column(name="product_name", type="string", length=255)
     */
    private $productName;

    /**
     * @var float
     *
     * @ORM\Column(name="set_up_price", type="integer", nullable=true)
     */
    private $setUpPrice;

    /**
     * @var float
     *
     * @ORM\Column(name="rental_unit_price", type="integer", nullable=true)
     */
    private $rentalUnitPrice;

    /**
     * @var float
     *
     * @ORM\Column(name="transport_unit_price", type="integer", nullable=true)
     */
    private $transportUnitPrice;

    /**
     * @var float
     *
     * @ORM\Column(name="treatment_unit_price", type="integer", nullable=true)
     */
    private $treatmentUnitPrice;

    /**
     * @var float
     *
     * @ORM\Column(name="traceability_unit_price", type="integer", nullable=true)
     */
    private $traceabilityUnitPrice;

    /**
     * @var float
     *
     * @ORM\Column(name="set_up_rate", type="bigint")
     */
    private $setUpRate;

    /**
     * @var float
     *
     * @ORM\Column(name="rental_rate", type="bigint")
     */
    private $rentalRate;

    /**
     * @var float
     *
     * @ORM\Column(name="transport_rate", type="bigint", nullable=true)
     */
    private $transportRate;

    /**
     * @var float
     *
     * @ORM\Column(name="treatment_rate", type="bigint", nullable=true)
     */
    private $treatmentRate;
    
    /**
     * @var float
     *
     * @ORM\Column(name="traceability_rate", type="bigint", nullable=true)
     */
    private $traceabilityRate;

    /**
     * @var int
     *
     * @ORM\Column(name="access_price", type="integer")
     */
    private $accessPrice;

    /**
     * @var float
     *
     * @ORM\Column(name="total_amount", type="integer")
     */
    private $totalAmount;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer")
     * @Assert\NotBlank()
     * @Assert\Type(type="integer", message="La quantité doit être un nombre entier")
     */
    private $quantity;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false)
     */
    private $product;
    
    /**
     * @var QuoteRequest
     *
     * @ORM\ManyToOne(targetEntity="QuoteRequest", inversedBy="quoteRequestLines")
     * @ORM\JoinColumn(name="quote_request_id", referencedColumnName="id", nullable=false)
     */
    private $quoteRequest;
    
    
    /**
     * QuoteRequestLine constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->dateCreation = new DateTime();
    }
    
    
    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
     * @param null $dateUpdate
     *
     * @return $this
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
     * @param null $deleted
     *
     * @return $this
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
     * @param $productName
     *
     * @return $this
     */
    public function setProductName($productName): self
    {
        $this->productName = $productName;

        return $this;
    }
    
    /**
     * @return string
     */
    public function getProductName(): string
    {
        return $this->productName;
    }
    
    /**
     * @param $totalAmount
     *
     * @return $this
     */
    public function setTotalAmount($totalAmount): self
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }
    
    /**
     * @return float
     */
    public function getTotalAmount(): float
    {
        return (float)$this->totalAmount;
    }
    
    /**
     * @param $quantity
     *
     * @return $this
     */
    public function setQuantity($quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }
    
    /**
     * @return int|null
     */
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }
    
    /**
     * @param Product $product
     *
     * @return $this
     */
    public function setProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }
    
    /**
     * @return Product|null
     */
    public function getProduct(): ?Product
    {
        return $this->product;
    }
    
    /**
     * @param QuoteRequest $quoteRequest
     *
     * @return $this
     */
    public function setQuoteRequest(QuoteRequest $quoteRequest): self
    {
        $this->quoteRequest = $quoteRequest;

        return $this;
    }
    
    /**
     * @return QuoteRequest
     */
    public function getQuoteRequest(): QuoteRequest
    {
        return $this->quoteRequest;
    }
    
    /**
     * @param null $rentalUnitPrice
     *
     * @return $this
     */
    public function setRentalUnitPrice($rentalUnitPrice = null): self
    {
        $this->rentalUnitPrice = $rentalUnitPrice;

        return $this;
    }
    
    /**
     * @return float|null
     */
    public function getRentalUnitPrice(): ?float
    {
        return (float)$this->rentalUnitPrice;
    }
    
    /**
     * @param null $transportUnitPrice
     *
     * @return $this
     */
    public function setTransportUnitPrice($transportUnitPrice = null): self
    {
        $this->transportUnitPrice = $transportUnitPrice;

        return $this;
    }
    
    /**
     * @return float|null
     */
    public function getTransportUnitPrice(): ?float
    {
        return (float)$this->transportUnitPrice;
    }
    
    /**
     * @param null $treatmentUnitPrice
     *
     * @return $this
     */
    public function setTreatmentUnitPrice($treatmentUnitPrice = null): self
    {
        $this->treatmentUnitPrice = $treatmentUnitPrice;

        return $this;
    }
    
    /**
     * @return float|null
     */
    public function getTreatmentUnitPrice(): ?float
    {
        return (float)$this->treatmentUnitPrice;
    }
    
    /**
     * @param null $traceabilityUnitPrice
     *
     * @return $this
     */
    public function setTraceabilityUnitPrice($traceabilityUnitPrice = null): self
    {
        $this->traceabilityUnitPrice = $traceabilityUnitPrice;

        return $this;
    }
    
    /**
     * @return float|null
     */
    public function getTraceabilityUnitPrice(): ?float
    {
        return (float)$this->traceabilityUnitPrice;
    }
    
    /**
     * @param $transportRate
     *
     * @return $this
     */
    public function setTransportRate($transportRate): self
    {
        $this->transportRate = $transportRate;

        return $this;
    }
    
    /**
     * @return float
     */
    public function getTransportRate(): float
    {
        return (float)$this->transportRate;
    }
    
    /**
     * @param $treatmentRate
     *
     * @return $this
     */
    public function setTreatmentRate($treatmentRate): self
    {
        $this->treatmentRate = $treatmentRate;

        return $this;
    }
    
    /**
     * @return float
     */
    public function getTreatmentRate(): float
    {
        return (float)$this->treatmentRate;
    }
    
    /**
     * @param $traceabilityRate
     *
     * @return $this
     */
    public function setTraceabilityRate($traceabilityRate): self
    {
        $this->traceabilityRate = $traceabilityRate;

        return $this;
    }
    
    /**
     * @return float
     */
    public function getTraceabilityRate(): float
    {
        return (float)$this->traceabilityRate;
    }
    
    /**
     * @param $setUpPrice
     *
     * @return $this
     */
    public function setSetUpPrice($setUpPrice): self
    {
        $this->setUpPrice = $setUpPrice;

        return $this;
    }
    
    /**
     * @return float|null
     */
    public function getSetUpPrice(): ?float
    {
        return (float)$this->setUpPrice;
    }
    
    /**
     * @param $setUpRate
     *
     * @return $this
     */
    public function setSetUpRate($setUpRate): self
    {
        $this->setUpRate = $setUpRate;

        return $this;
    }
    
    /**
     * @return float
     */
    public function getSetUpRate(): float
    {
        return (float)$this->setUpRate;
    }
    
    /**
     * @param $rentalRate
     *
     * @return $this
     */
    public function setRentalRate($rentalRate): self
    {
        $this->rentalRate = $rentalRate;

        return $this;
    }
    
    /**
     * @return float
     */
    public function getRentalRate(): float
    {
        return (float)$this->rentalRate;
    }
    
    /**
     * @param $accessPrice
     *
     * @return $this
     */
    public function setAccessPrice($accessPrice): self
    {
        $this->accessPrice = $accessPrice;

        return $this;
    }
    
    /**
     * @return int
     */
    public function getAccessPrice(): int
    {
        return $this->accessPrice;
    }
}
