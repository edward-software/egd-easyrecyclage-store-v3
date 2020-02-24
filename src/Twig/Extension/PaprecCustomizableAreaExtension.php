<?php
declare(strict_types=1);

namespace Twig\Extension;

use App\Entity\CustomArea;
use App\Service\CustomAreaManager;
use Exception;
use Symfony\Component\DependencyInjection\Container;
use Twig\TwigFunction;

class PaprecCustomizableAreaExtension extends AbstractExtension
{

    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
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
            /** @var CustomAreaManager $customAreaManager */
            $customAreaManager = $this->container->get('paprec_catalog.custom_area_manager');

            return $customAreaManager->getByCodeLocale($code, $locale);
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
