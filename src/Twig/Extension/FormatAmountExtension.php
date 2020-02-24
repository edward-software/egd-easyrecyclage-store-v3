<?php
declare(strict_types=1);

namespace Twig\Extension;

use Exception;
use Symfony\Component\DependencyInjection\Container;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class FormatAmountExtension extends AbstractExtension
{

    private $container;
    
    /**
     * FormatAmountExtension constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    
    /**
     * @return array|TwigFilter[]
     */
    public function getFilters()
    {
        return array(
            new TwigFunction('formatAmount', [$this, 'formatAmount']),
        );
    }
    
    /**
     * @param $amount
     * @param $locale
     * @param null $currency
     * @param null $type
     *
     * @return string
     * @throws Exception
     */
    public function formatAmount($amount, $locale, $currency = null, $type = null)
    {
        if ($type === 'PERCENTAGE') {
            $currency = 'PERCENTAGE';
        }

        $formatManager = $this->container->get('paprec_catalog.number_manager');
        
        if ($type === 'FORMAT15') {
            return $formatManager->formatAmount15($amount, $locale);
        }

        if ($type === 'DEC2') {
            $amount = str_replace(',', '.', $amount);
            return  number_format((float)$amount, 2);
        }

        return $formatManager->formatAmount($amount, $currency, $locale);

    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return 'formatAmount';
    }
}
