<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Region;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RegionManager
{

    private $em;
    private $container;
    
    
    /**
     * RegionManager constructor.
     *
     * @param EntityManagerInterface $em
     * @param ContainerInterface $container
     */
    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }
    
    
    /**
     * @param $region
     *
     * @return Region
     * @throws Exception
     */
    public function get($region)
    {
        $id = $region;
        
        if ($region instanceof Region) {
            $id = $region->getId();
        }
        
        try {
            
            /** @var Region $region */
            $region = $this->em->getRepository(Region::class)->find($id);

            if ($region === null || $this->isDeleted($region)) {
                throw new EntityNotFoundException('regionNotFound');
            }

            return $region;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'à ce jour le region  n'est pas supprimée
     *
     * @param Region $region
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     * @throws Exception
     */
    public function isDeleted(Region $region, $throwException = false)
    {
        $now = new DateTime();
        $deleted = $region->getDeleted();

        if ($region->getDeleted() !== null && $deleted instanceof DateTime && $deleted < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('regionNotFound');
            }
            
            return true;
        }
        
        return false;
    }
}
