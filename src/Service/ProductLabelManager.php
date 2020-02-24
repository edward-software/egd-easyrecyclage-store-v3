<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\ProductLabel;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductLabelManager
{

    private $em;
    private $container;
    
    
    /**
     * ProductLabelManager constructor.
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
     * @param $productLabel
     *
     * @return object|null
     * @throws Exception
     */
    public function get($productLabel)
    {
        $id = $productLabel;
        
        if ($productLabel instanceof ProductLabel) {
            $id = $productLabel->getId();
        }
        
        try {
            
            /** @var ProductLabel $productLabel */
            $productLabel = $this->em->getRepository(ProductLabel::class)->find($id);

            /**
             * Vérification que le produitLabel existe ou ne soit pas supprimé
             */
            if ($productLabel === null || $this->isDeleted($productLabel)) {
                throw new EntityNotFoundException('productLabelNotFound');
            }
            
            return $productLabel;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérifie qu'à ce jour, le produitLabel ce soit pas supprimé
     *
     * @param ProductLabel $productLabel
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     * @throws Exception
     */
    public function isDeleted(ProductLabel $productLabel, $throwException = false)
    {
        $now = new DateTime();
        $deleted = $productLabel->getDeleted();

        if ($productLabel->getDeleted() !== null && $deleted instanceof DateTime && $deleted < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('productLabelNotFound');
            }

            return true;
        }
        
        return false;
    }
}
