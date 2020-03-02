<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\PostalCode;
use App\Entity\Product;
use App\Entity\ProductLabel;
use App\Entity\QuoteRequest;
use App\Entity\QuoteRequestLine;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductManager
{

    private $em;
    private $container;
    
    
    /**
     * ProductManager constructor.
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
     * @param $product
     *
     * @return Product
     * @throws Exception
     */
    public function get($product)
    {
        $id = $product;
        
        if ($product instanceof Product) {
            $id = $product->getId();
        }
        
        try {

            /** @var Product $product */
            $product = $this->em->getRepository(Product::class)->find($id);

            /**
             * Vérification que le produit existe ou ne soit pas supprimé
             */
            if ($product === null || $this->isDeleted($product)) {
                throw new EntityNotFoundException('productNotFound');
            }


            return $product;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérifie qu'à ce jour, le produit ce soit pas supprimé
     *
     * @param Product $product
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     * @throws Exception
     */
    public function isDeleted(Product $product, $throwException = false)
    {
        $now = new DateTime();
        $deleted = $product->getDeleted();

        if ($product->getDeleted() !== null && $deleted instanceof DateTime && $deleted < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('productNotFound');
            }

            return true;
        }
        
        return false;
    }

    /**
     * On passe en paramètre les options Category et PostalCode, retourne les produits qui appartiennent à la catégorie
     * et qui sont disponibles dans le postalCode
     * @param $options
     * @return array
     * @throws Exception
     */
    public function findAvailables($options)
    {
        $categoryId = $options['category'];
        $postalCode = $options['postalCode'];

        // TODO class ProductCategory doesn't exists (maybe delete this part ?)
//        try {
//            /** @var QueryBuilder $query */
//            $query = $this->em
//                ->getRepository(Product::class)
//                ->createQueryBuilder('p')
//                ->innerJoin(ProductCategory::class, 'pc', Join::WITH, 'p.id = pc.product')
//                ->where('pc.category = :category')
//                ->orderBy('pc.position', 'ASC')
//                ->setParameter("category", $categoryId);
//
//            $products = $query->getQuery()->getResult();
//
//
//            $productsPostalCodeMatch = [];
//
//
//            // On parcourt tous les produits DI pour récupérer ceux  qui possèdent le postalCode
//            /** @var Product $product */
//            foreach ($products as $product) {
//                $postalCodes = str_replace(' ', '', $product->getAvailablePostalCodes());
//                $postalCodesArray = explode(',', $postalCodes);
//                foreach ($postalCodesArray as $pC) {
//                    //on teste juste les deux premiers caractères pour avoir le code du département
//                    if (substr($pC, 0, 2) == substr($postalCode, 0, 2)) {
//                        $productsPostalCodeMatch[] = $product;
//                    }
//                }
//            }
//
//            return $productsPostalCodeMatch;
//
//        } catch (ORMException $e) {
//            throw new Exception('unableToGetProducts', 500);
//        } catch (Exception $e) {
//            throw new Exception($e->getMessage(), $e->getCode());
//        }
        
        return [];
    }

    /**
     * Fonction calculant le prix d'un produit en fonction de sa quantité, du code postal
     * Utilisée dans le calcul du montant d'un Cart et dans le calcul du montant d'une ligne ProductQuoteLine
     * Si le calcul est modifiée, il faudra donc le modifier uniquement ici
     *
     * @param PostalCode $pC
     * @param Product $product
     * @param $qtty
     * @return float|int
     * @throws Exception
     */
    public function calculatePrice(QuoteRequestLine $quoteRequestLine, NumberManager $numberManager)
    {
        
        return ($numberManager->denormalize($quoteRequestLine->getSetUpPrice()) * $numberManager->denormalize15($quoteRequestLine->getSetUpRate())
                + $numberManager->denormalize($quoteRequestLine->getRentalUnitPrice()) * $numberManager->denormalize15($quoteRequestLine->getRentalRate())
                + $numberManager->denormalize($quoteRequestLine->getTransportUnitPrice()) * $numberManager->denormalize15($quoteRequestLine->getTransportRate())
                + $numberManager->denormalize($quoteRequestLine->getTreatmentUnitPrice()) * $numberManager->denormalize15($quoteRequestLine->getTreatmentRate())
                + $numberManager->denormalize($quoteRequestLine->getTraceabilityUnitPrice()) * $numberManager->denormalize15($quoteRequestLine->getTraceabilityRate())
                + $this->getAccesPrice($quoteRequestLine->getQuoteRequest()))
            * $quoteRequestLine->getQuantity()
            * (1 + $numberManager->denormalize($quoteRequestLine->getQuoteRequest()->getOverallDiscount() / 100));

    }

    /**
     * Renvoi un prix fixe en fonction des conditions d'accès
     *
     * @param QuoteRequestLine $quoteRequestLine
     * @return int|mixed
     */
    public function getAccesPrice(QuoteRequest $quoteRequest)
    {
        if ($quoteRequest && $quoteRequest->getAccess()) {
            $prices = [];
            foreach ($this->container->getParameter('paprec_quote_access_price') as $p => $value) {
                $prices[$p] = $value;
            }
            switch ($quoteRequest->getAccess()) {
                case 'stairs':
                    return $prices['stairs'];
                    break;
                case 'elevator':
                    return $prices['elevator'];
                    break;
                case 'ground':
                    return $prices['ground'];
                    break;
                default:
                    return 0;
            }
        }
        return 0;
    }

    public function getProductLabels($product)
    {
        $id = $product;
        
        if ($product instanceof Product) {
            $id = $product->getId();
        }
        
        try {

            /** @var ProductLabel[] $productLabels */
            $productLabels = $this->em->getRepository(ProductLabel::class)->findBy([
                'product' => $product,
                'deleted' => null
            ]);

            /**
             * Vérification que le produit existe ou ne soit pas supprimé
             */
            if (empty($productLabels)) {
                throw new EntityNotFoundException('productLabelsNotFound');
            }
            
            return $productLabels;
            
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

    }
    
    /**
     * @param Product $product
     * @param $language
     *
     * @return object|null
     * @throws Exception
     */
    public function getProductLabelByProductAndLocale(Product $product, $language)
    {
        $id = $product;
        
        if ($product instanceof Product) {
            $id = $product->getId();
        }
        
        try {

            /** @var Product $product */
            $product = $this->em->getRepository(Product::class)->find($id);

            /**
             * Vérification que le produit existe ou ne soit pas supprimé
             */
            if ($product === null || $this->isDeleted($product)) {
                throw new EntityNotFoundException('productNotFound');
            }

            /** @var ProductLabel $productLabel */
            $productLabel = $this->em->getRepository(ProductLabel::class)->findOneBy([
                'product' => $product,
                'language' => $language
            ]);

            /**
             * Si il y'en a pas dans la langue de la locale, on en prend un au hasard
             */
            if ($productLabel === null || $this->IsDeletedProductLabel($productLabel)) {
                $productLabel = $this->em->getRepository(ProductLabel::class)->findOneBy([
                    'product' => $product
                ]);

                if ($productLabel === null || $this->IsDeletedProductLabel($productLabel)) {
                    throw new EntityNotFoundException('productLabelNotFound');
                }
            }
            
            return $productLabel;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérifie qu'à ce jour, le libellé produit ne soit pas supprimé
     *
     * @param ProductLabel $productLabel
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     * @throws Exception
     */
    public function isDeletedProductLabel(ProductLabel $productLabel, $throwException = false)
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
    
    /**
     * @return mixed
     */
    public function getAvailableProducts()
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->em->getRepository(Product::class)->createQueryBuilder('p');

        $queryBuilder->select(['p'])
            ->where('p.deleted IS NULL')
            ->andWhere('p.isEnabled = 1')
            ->orderBy('p.position');

        return $queryBuilder->getQuery()->getResult();
    }
}
