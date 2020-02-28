<?php
declare(strict_types=1);

namespace App\Twig;

use App\Entity\CustomArea;
use App\Service\CustomAreaManager;
use Exception;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PaprecCustomizableAreaExtension extends AbstractExtension
{

    private $customAreaManager;

    
    public function __construct(CustomAreaManager $customAreaManager)
    {
        $this->customAreaManager = $customAreaManager;
    }

    
    /**
     * @return array|\Twig_Function[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('paprec_customizable_area', [$this, 'customizableArea']),
        ];
    }

    /**
     * @param $code
     * @return array|object[]|CustomArea[]
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
        return 'paprec_customizable_area';
    }
}
