<?php
declare(strict_types=1);

namespace App\Twig\Extension;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AccessExtension extends AbstractExtension
{

    private $container;
    private $token;
    
    
    /**
     * AccessExtension constructor.
     *
     * @param Container $container
     * @param TokenStorage $token
     */
    public function __construct(Container $container, TokenStorage $token)
    {
        $this->container = $container;
        $this->token = $token;
    }

    
    /**
     * @return array|TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('paprec_has_access', [$this, 'hasAccess']),
        ];
    }
    
    /**
     * @param $role
     * @param null $division
     * @return bool
     */
    public function hasAccess($role, $division = null)
    {
        $token = $this->token->getToken();
        
        if ($token->isAuthenticated() && $token->getUser()) {
            if ($division && $division != null) {
                if(!$this->container->get('security.authorization_checker')->isGranted($role)) {
                    
                    return false;
                }
            }
        }
        
        return true;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'paprec_has_access';
    }
}
