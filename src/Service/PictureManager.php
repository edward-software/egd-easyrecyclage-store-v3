<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Picture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PictureManager
{

    private $em;
    private $container;
    
    
    /**
     * PictureManager constructor.
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
     * @param $picture
     *
     * @return Picture
     * @throws Exception
     */
    public function get($picture)
    {
        $id = $picture;
        
        if ($picture instanceof Picture) {
            $id = $picture->getId();
        }
        
        try {

            /** @var Picture $picture */
            $picture = $this->em->getRepository(Picture::class)->find($id);

            /**
             * Vérification que le produit existe ou ne soit pas supprimé
             */
            if ($picture === null) {
                throw new EntityNotFoundException('pictureNotFound');
            }

            return $picture;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
