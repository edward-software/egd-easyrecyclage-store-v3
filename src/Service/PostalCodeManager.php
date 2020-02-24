<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\PostalCode;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PostalCodeManager
{

    private $em;
    private $container;
    
    /**
     * PostalCodeManager constructor.
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
     * @param $postalCode
     *
     * @return object|null
     * @throws Exception
     */
    public function get($postalCode)
    {
        $id = $postalCode;
        
        if ($postalCode instanceof PostalCode) {
            $id = $postalCode->getId();
        }
        
        try {

            /** @var PostalCode $postalCode */
            $postalCode = $this->em->getRepository(PostalCode::class)->find($id);

            if ($postalCode === null || $this->isDeleted($postalCode)) {
                throw new EntityNotFoundException('postalCodeNotFound');
            }

            return $postalCode;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'à ce jour le postalCode  n'est pas supprimé
     *
     * @param PostalCode $postalCode
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     * @throws Exception
     */
    public function isDeleted(PostalCode $postalCode, $throwException = false)
    {
        $now = new DateTime();
        $deleted = $postalCode->getDeleted();

        if ($postalCode->getDeleted() !== null && $deleted instanceof DateTime && $deleted < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('postalCodeNotFound');
            }
            
            return true;
        }
        
        return false;
    }
}
