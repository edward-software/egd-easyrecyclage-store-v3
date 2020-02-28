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
     * @var int
     *
     * @ORM\Column(name="set_up_price", type="integer", nullable=true)
     */
    private $setUpPrice;

    /**
     * @var int
     *
     * @ORM\Column(name="rental_unit_price", type="integer", nullable=true)
     */
    private $rentalUnitPrice;

    /**
     * @var int
     *
     * @ORM\Column(name="transport_unit_price", type="integer", nullable=true)
     */
    private $transportUnitPrice;

    /**
     * @var int
     *
     * @ORM\Column(name="treatment_unit_price", type="integer", nullable=true)
     */
    private $treatmentUnitPrice;

    /**
     * @var int
     *
     * @ORM\Column(name="traceability_unit_price", type="integer", nullable=true)
     */
    private $traceabilityUnitPrice;

    /**
     * @var int
     *
     * @ORM\Column(name="set_up_rate", type="bigint")
     */
    private $setUpRate;

    /**
     * @var int
     *
     * @ORM\Column(name="rental_rate", type="bigint")
     */
    private $rentalRate;

    /**
     * @var int
     *
     * @ORM\Column(name="transport_rate", type="bigint", nullable=true)
     */
    private $transportRate;

    /**
     * @var int
     *
     * @ORM\Column(name="treatment_rate", type="bigint", nullable=true)
     */
    private $treatmentRate;
    
    /**
     * @var int
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
     * @var int
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
     * Get id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set dateCreation.
     *
     * @param DateTime $dateCreation
     *
     * @return QuoteRequestLine
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
    public function getDateCreation() : DateTime
    {
        return $this->dateCreation;
    }

    /**
     * Set dateUpdate.
     *
     * @param DateTime|null $dateUpdate
     *
     * @return QuoteRequestLine
     */
    public function setDateUpdate($dateUpdate = null) : self
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
     * @return QuoteRequestLine
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
     * Set productName.
     *
     * @param string $productName
     *
     * @return QuoteRequestLine
     */
    public function setProductName($productName) : self
    {
        $this->productName = $productName;

        return $this;
    }

    /**
     * Get productName.
     *
     * @return string
     */
    public function getProductName() : string
    {
        return $this->productName;
    }

    /**
     * Set totalAmount.
     *
     * @param int $totalAmount
     *
     * @return QuoteRequestLine
     */
    public function setTotalAmount($totalAmount) : self
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    /**
     * Get totalAmount.
     *
     * @return int
     */
    public function getTotalAmount() : int
    {
        return $this->totalAmount;
    }

    /**
     * Set quantity.
     *
     * @param int $quantity
     *
     * @return QuoteRequestLine
     */
    public function setQuantity($quantity) : self
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity.
     *
     * @return int
     */
    public function getQuantity() : int
    {
        return $this->quantity;
    }

    /**
     * Set product.
     *
     * @param Product $product
     *
     * @return QuoteRequestLine
     */
    public function setProduct(Product $product) : self
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product.
     *
     * @return Product
     */
    public function getProduct() : Product
    {
        return $this->product;
    }

    /**
     * Set quoteRequest.
     *
     * @param QuoteRequest $quoteRequest
     *
     * @return QuoteRequestLine
     */
    public function setQuoteRequest(QuoteRequest $quoteRequest) : self
    {
        $this->quoteRequest = $quoteRequest;

        return $this;
    }

    /**
     * Get quoteRequest.
     *
     * @return QuoteRequest
     */
    public function getQuoteRequest() : QuoteRequest
    {
        return $this->quoteRequest;
    }

    /**
     * Set rentalUnitPrice.
     *
     * @param int|null $rentalUnitPrice
     *
     * @return QuoteRequestLine
     */
    public function setRentalUnitPrice($rentalUnitPrice = null) : self
    {
        $this->rentalUnitPrice = $rentalUnitPrice;

        return $this;
    }

    /**
     * Get rentalUnitPrice.
     *
     * @return int|null
     */
    public function getRentalUnitPrice() : ?int
    {
        return $this->rentalUnitPrice;
    }

    /**
     * Set transportUnitPrice.
     *
     * @param int|null $transportUnitPrice
     *
     * @return QuoteRequestLine
     */
    public function setTransportUnitPrice($transportUnitPrice = null) : self
    {
        $this->transportUnitPrice = $transportUnitPrice;

        return $this;
    }

    /**
     * Get transportUnitPrice.
     *
     * @return int|null
     */
    public function getTransportUnitPrice() : ?int
    {
        return $this->transportUnitPrice;
    }

    /**
     * Set treatmentUnitPrice.
     *
     * @param int|null $treatmentUnitPrice
     *
     * @return QuoteRequestLine
     */
    public function setTreatmentUnitPrice($treatmentUnitPrice = null) : self
    {
        $this->treatmentUnitPrice = $treatmentUnitPrice;

        return $this;
    }

    /**
     * Get treatmentUnitPrice.
     *
     * @return int|null
     */
    public function getTreatmentUnitPrice() : ?int
    {
        return $this->treatmentUnitPrice;
    }

    /**
     * Set traceabilityUnitPrice.
     *
     * @param int|null $traceabilityUnitPrice
     *
     * @return QuoteRequestLine
     */
    public function setTraceabilityUnitPrice($traceabilityUnitPrice = null) : self
    {
        $this->traceabilityUnitPrice = $traceabilityUnitPrice;

        return $this;
    }

    /**
     * Get traceabilityUnitPrice.
     *
     * @return int|null
     */
    public function getTraceabilityUnitPrice() : ?int
    {
        return $this->traceabilityUnitPrice;
    }

    /**
     * Set transportRate.
     *
     * @param int $transportRate
     *
     * @return QuoteRequestLine
     */
    public function setTransportRate($transportRate) : self
    {
        $this->transportRate = $transportRate;

        return $this;
    }

    /**
     * Get transportRate.
     *
     * @return int
     */
    public function getTransportRate() : int
    {
        return $this->transportRate;
    }

    /**
     * Set treatmentRate.
     *
     * @param int $treatmentRate
     *
     * @return QuoteRequestLine
     */
    public function setTreatmentRate($treatmentRate) : self
    {
        $this->treatmentRate = $treatmentRate;

        return $this;
    }

    /**
     * Get treatmentRate.
     *
     * @return int
     */
    public function getTreatmentRate() : int
    {
        return $this->treatmentRate;
    }

    /**
     * Set traceabilityRate.
     *
     * @param int $traceabilityRate
     *
     * @return QuoteRequestLine
     */
    public function setTraceabilityRate($traceabilityRate) : self
    {
        $this->traceabilityRate = $traceabilityRate;

        return $this;
    }

    /**
     * Get traceabilityRate.
     *
     * @return int
     */
    public function getTraceabilityRate() : int
    {
        return $this->traceabilityRate;
    }

    /**
     * Set setUpPrice.
     *
     * @param int $setUpPrice
     *
     * @return QuoteRequestLine
     */
    public function setSetUpPrice($setUpPrice) : self
    {
        $this->setUpPrice = $setUpPrice;

        return $this;
    }

    /**
     * Get setUpPrice.
     *
     * @return int
     */
    public function getSetUpPrice() : int
    {
        return $this->setUpPrice;
    }

    /**
     * Set setUpRate.
     *
     * @param int $setUpRate
     *
     * @return QuoteRequestLine
     */
    public function setSetUpRate($setUpRate) : self
    {
        $this->setUpRate = $setUpRate;

        return $this;
    }

    /**
     * Get setUpRate.
     *
     * @return int
     */
    public function getSetUpRate() : int
    {
        return $this->setUpRate;
    }

    /**
     * Set rentalRate.
     *
     * @param int $rentalRate
     *
     * @return QuoteRequestLine
     */
    public function setRentalRate($rentalRate) : self
    {
        $this->rentalRate = $rentalRate;

        return $this;
    }

    /**
     * Get rentalRate.
     *
     * @return int
     */
    public function getRentalRate() : int
    {
        return $this->rentalRate;
    }

    /**
     * Set accessPrice.
     *
     * @param int $accessPrice
     *
     * @return QuoteRequestLine
     */
    public function setAccessPrice($accessPrice) : self
    {
        $this->accessPrice = $accessPrice;

        return $this;
    }

    /**
     * Get accessPrice.
     *
     * @return int
     */
    public function getAccessPrice() : int
    {
        return $this->accessPrice;
    }
}
