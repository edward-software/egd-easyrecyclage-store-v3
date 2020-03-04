<?php
declare(strict_types=1);

namespace App\Twig;

use App\Entity\CustomArea;
use App\Service\CustomAreaManager;
use Exception;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CustomizableAreaExtension extends AbstractExtension
{

    private $customAreaManager;
    
    
    /**
     * PaprecCustomizableAreaExtension constructor.
     *
     * @param CustomAreaManager $customAreaManager
     */
    public function __construct(CustomAreaManager $customAreaManager)
    {
        $this->customAreaManager = $customAreaManager;
    }
    
    
    /**
     * @return array|TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('customizable_area', [$this, 'customizableArea']),
        ];
    }
    
    /**
     * @param $code
     * @param $locale
     *
     * @return CustomArea|object|null
     * @throws Exception
     */
    public function customizableArea($code, $locale)
    {
        try {
            $locale = strtoupper($locale);
            
            return $this->customAreaManager->getByCodeLocale($code, $locale);
            
        } catch (Exception $e) {
            
            throw new Exception($e->getMessage(), $e->getCode());
        }


    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'customizable_area';
    }
}
