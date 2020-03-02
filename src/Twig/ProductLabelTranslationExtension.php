<?php
declare(strict_types=1);

namespace App\Twig;

use App\Entity\Product;
use App\Service\ProductManager;
use Exception;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ProductLabelTranslationExtension extends AbstractExtension
{

    private $productManager;

    
    public function __construct(ProductManager $productManager)
    {
        $this->productManager = $productManager;
    }
    
    
    /**
     * @return array|TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('productLabelTranslation', [$this, 'productLabelTranslation']),
        ];
    }
    
    /**
     * @param Product $product
     * @param $lang
     * @param null $attr
     *
     * @return string
     */
    public function productLabelTranslation(Product $product, $lang, $attr = null)
    {
        $returnLabel = '';
        
        try {
            
            /** @var Product $product */
            $product = $this->productManager->get($product);
            
            switch ($attr) {
                case 'shortDescription':
                    $returnLabel = $this->productManager->getProductLabelByProductAndLocale($product, $lang)->getShortDescription();
                    break;
                case 'version':
                    $returnLabel = $this->productManager->getProductLabelByProductAndLocale($product, $lang)->getVersion();
                    break;
                case 'lockType':
                    $returnLabel = $this->productManager->getProductLabelByProductAndLocale($product, $lang)->getLockType();
                    break;
                default:
                    $returnLabel = $this->productManager->getProductLabelByProductAndLocale($product, $lang)->getName();
            }
        } catch (Exception $e) {
        
        }

        return $returnLabel;
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return 'formatId';
    }
}
