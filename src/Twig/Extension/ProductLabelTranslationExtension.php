<?php
declare(strict_types=1);

namespace Twig\Extension;

use App\Entity\Product;
use App\Service\ProductManager;
use Exception;
use Symfony\Component\DependencyInjection\Container;
use Twig\TwigFilter;

class ProductLabelTranslationExtension extends AbstractExtension
{

    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('productLabelTranslation', [$this, 'productLabelTranslation']),
        );
    }

    public function productLabelTranslation(Product $product, $lang, $attr = null)
    {
        $returnLabel = '';
        
        try {
            /** @var ProductManager $productManager */
            $productManager = $this->container->get('paprec_catalog.product_manager');
            
            $product = $productManager->get($product);
            
            switch ($attr) {
                case 'shortDescription':
                    $returnLabel = $productManager->getProductLabelByProductAndLocale($product, $lang)->getShortDescription();
                    break;
                case 'version':
                    $returnLabel = $productManager->getProductLabelByProductAndLocale($product, $lang)->getVersion();
                    break;
                case 'lockType':
                    $returnLabel = $productManager->getProductLabelByProductAndLocale($product, $lang)->getLockType();
                    break;
                default:
                    $returnLabel = $productManager->getProductLabelByProductAndLocale($product, $lang)->getName();
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
