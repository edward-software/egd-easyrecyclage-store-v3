<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Cart;
use App\Entity\Product;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use \Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CartManager
{
    
    private $em;
    private $container;
    
    
    /**
     * CartManager constructor.
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
     * Retourne un Cart en passant son Id ou un object Cart
     * @param $cart
     *
     * @return Cart
     * @throws Exception
     */
    public function get($cart)
    {
        $id = $cart;
        
        if ($cart instanceof Cart) {
            $id = $cart->getId();
        }
        
        try {
            
            /** @var Cart $cart */
            $cart = $this->em->getRepository(Cart::class)->find($id);

            if ($cart === null || $this->isDisabled($cart)) {
                throw new EntityNotFoundException('cartNotFound', 404);
            }

            return $cart;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'a ce jour, le cart ne soit pas désactivé
     *
     * @param Cart $cart
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     * @throws Exception
     */
    public function isDisabled(Cart $cart, $throwException = false)
    {
        $now = new \DateTime();
        $disabled = $cart->getDisabled();

        if ($cart->getDisabled() !== null && $disabled instanceof DateTime && $disabled < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('cartNotFound');
            }

            return true;
        }
        
        return false;
    }

    /**
     * Créé un nouveau Cart en initialisant sa date Disabled  dans Today + $deltaJours
     *
     * @param $deltaJours
     * @return Cart
     * @throws Exception
     */
    public function create($deltaJours)
    {
        try {

            $cart = new Cart();

            /**
             * Initialisant de $disabled
             */
            $now = new \DateTime();
            $disabledDate = $now->modify('+' . $deltaJours . 'day');
            $cart->setDisabled($disabledDate);
            
            $this->em->persist($cart);
            $this->em->flush();

            return $cart;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
    
    /**
     * Ajoute du content au cart pour un produit et une quantité donnés
     *
     * @param $id
     * @param $productId
     * @param $quantity
     * @return mixed
     * @throws Exception
     */
    public function addContent($id, Product $product, $quantity)
    {
        /** @var Cart $cart */
        $cart = $this->get($id);

        $content = $cart->getContent();
        $newContent = ['pId' => $product->getId(), 'qtty' => $quantity];
        
        if ($content && count($content)) {
            foreach ($content as $key => $value) {
                if ($value['pId'] == $product->getId()) {
                    unset($content[$key]);
                }
            }
        }

        $content[] = $newContent;
        $cart->setContent($content);
        $this->em->flush();
        
        return $cart;
    }
    
    /**
     * Supprime un produit
     * @param $id
     * @param $productId
     * @return null|object|Cart
     * @throws Exception
     */
    public function removeContent($id, $productId)
    {
        /** @var Cart $cart */
        $cart = $this->get($id);
        
        $products = $cart->getContent();
        
        if ($products && count($products)) {
            foreach ($products as $key => $product) {
                if ($product['pId'] == $productId) {
                    unset($products[$key]);
                }
            }
        }
        
        $cart->setContent($products);
        $this->em->flush();
        
        return $cart;
    }
    
    /**
     * @param $id
     * @param $frequency
     * @param $frequencyTimes
     * @param $frequencyInterval
     * @throws Exception
     */
    public function addFrequency($id, $frequency, $frequencyTimes, $frequencyInterval)
    {
        /** @var Cart $cart */
        $cart = $this->get($id);

        if ($frequency === 'regular' || $frequency === 'ponctual') {
            $cart->setFrequency($frequency);
            $cart->setFrequencyTimes($frequencyTimes);
            $cart->setFrequencyInterval($frequencyInterval);
            $this->em->flush();
        } else {
            throw new Exception('frequency_invalid');
        }
    }
    
    /**
     * Ajoute du content au cart pour un produit
     *
     * @param $id
     * @param $productId
     * @return string
     * @throws Exception
     */
    public function addOneProduct($id, $productId)
    {
        /** @var Cart $cart */
        $cart = $this->get($id);
        
        $qtty = '1';
        $content = $cart->getContent();
        
        if ($content && count($content)) {
            foreach ($content as $key => $product) {
                if ($product['pId'] == $productId) {
                    $qtty = (string)((int)$product['qtty'] + 1);
                    unset($content[$key]);
                }
            }
        }
        
        $product = ['pId' => $productId, 'qtty' => $qtty];
        $content[] = $product;

        $cart->setContent($content);
        $this->em->persist($cart);
        $this->em->flush();
        
        return $qtty;
    }

    /**
     * Elève de 1 la quantité au Cart pour un produit
     *
     * @param $id
     * @param $productId
     * @param $quantity
     * @return string
     * @throws Exception
     */
    public function removeOneProduct($id, $productId)
    {
        /** @var Cart $cart */
        $cart = $this->get($id);
        
        $qtty = '0';
        $content = $cart->getContent();
        
        if ($content && count($content)) {
            foreach ($content as $key => $product) {
                if ($product['pId'] == $productId) {
                    $qtty = (string)((int)$product['qtty'] - 1);
                    unset($content[$key]);
                }
            }
        }

        if ($qtty !== '0') {
            $product = ['pId' => $productId, 'qtty' => $qtty];
            $content[] = $product;
        }

        $cart->setContent($content);
        $this->em->persist($cart);
        $this->em->flush();
        
        return $qtty;
    }
}
