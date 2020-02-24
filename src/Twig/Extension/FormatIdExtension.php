<?php
declare(strict_types=1);

namespace Twig\Extension;

use Symfony\Component\DependencyInjection\Container;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class FormatIdExtension extends AbstractExtension
{

    private $container;
    
    /**
     * FormatIdExtension constructor.
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
            new TwigFunction('formatId', [$this, 'formatId']),
        );
    }
    
    /**
     * @param $id
     * @param $padlength
     * @param int $padstring
     * @param int $pad_type
     *
     * @return string
     */
    public function formatId($id, $padlength, $padstring = 0, $pad_type = STR_PAD_LEFT)
    {
        return str_pad($id, $padlength, $padstring, $pad_type);
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return 'formatId';
    }
}
