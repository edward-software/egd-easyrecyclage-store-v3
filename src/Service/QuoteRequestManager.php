<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\QuoteRequest;
use App\Entity\QuoteRequestLine;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Exception;
use iio\libmergepdf\Merger;
use Knp\Snappy\Pdf;
use Swift_Attachment;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Contracts\Translation\TranslatorInterface;

class QuoteRequestManager
{

    private $em;
    
    /**
     * QuoteRequestManager constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    
    /**
     * @param      $quoteRequest
     * @param bool $throwException
     *
     * @return QuoteRequest|null
     * @throws Exception
     */
    public function get($quoteRequest, $throwException = true)
    {
        $id = $quoteRequest;
        
        if ($quoteRequest instanceof QuoteRequest) {
            $id = $quoteRequest->getId();
        }
        
        try {
            /** @var QuoteRequest $quoteRequest */
            $quoteRequest = $this->em->getRepository(QuoteRequest::class)->find($id);

            // Vérification que le quoteRequest existe ou ne soit pas supprimé
            if ($quoteRequest === null || $this->isDeleted($quoteRequest)) {
                throw new EntityNotFoundException('quoteRequestNotFound');
            }
            
            return $quoteRequest;

        } catch (Exception $e) {
            if ($throwException) {
                throw new Exception($e->getMessage(), $e->getCode());
            }
    
            return null;
        }
    }
    
    /**
     * @param $reference
     *
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getCountByReference($reference)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->em->getRepository(QuoteRequest::class)
            ->createQueryBuilder('qr')
            ->select('count(qr)')
            ->where('qr.reference LIKE :ref')
            ->andWhere('qr.deleted IS NULL')
            ->setParameter('ref', $reference . '%');

        $count = $qb->getQuery()->getSingleScalarResult();

        if ($count != null) {
            return (int)$count + 1;
        }
    
        return 1;
    }
    
    /**
     * @param QuoteRequest $quoteRequest
     * @param bool         $throwException
     *
     * @return bool
     * @throws EntityNotFoundException
     */
    public function isDeleted(QuoteRequest $quoteRequest, $throwException = false)
    {
        $now = new DateTime();
        $deleted = $quoteRequest->getDeleted();

        if ($quoteRequest->getDeleted() !== null && $deleted instanceof DateTime && $deleted < $now) {
            if ($throwException) {
                throw new EntityNotFoundException('quoteRequestNotFound');
            }

            return true;
        }
        
        return false;
    }
    
    /**
     * @param QuoteRequest     $quoteRequest
     * @param QuoteRequestLine $quoteRequestLine
     * @param NumberManager    $numberManager
     * @param ProductManager   $productManager
     * @param null             $user
     * @param bool             $doFlush
     *
     * @throws Exception
     */
    public function addLine(
        QuoteRequest $quoteRequest,
        QuoteRequestLine $quoteRequestLine,
        NumberManager $numberManager,
        ProductManager $productManager,
        $user = null,
        $doFlush = true
    )
    {
        // On check s'il existe déjà une ligne pour ce produit, pour l'incrémenter
        /** @var QuoteRequestLine $currentQuoteLine */
        $currentQuoteLine = $this->em->getRepository(QuoteRequestLine::class)->findOneBy([
            'quoteRequest' => $quoteRequest,
            'product' => $quoteRequestLine->getProduct()
        ]);

        if ($currentQuoteLine) {
            $quantity = $quoteRequestLine->getQuantity() + $currentQuoteLine->getQuantity();
            $currentQuoteLine->setQuantity($quantity);

            // On recalcule le montant total de la ligne ainsi que celui du devis complet
            $totalLine = $this->calculateTotalLine($currentQuoteLine, $numberManager, $productManager);
            $currentQuoteLine->setTotalAmount($totalLine);
            $this->em->flush();
        } else {
            $quoteRequestLine->setQuoteRequest($quoteRequest);
            $quoteRequest->addQuoteRequestLine($quoteRequestLine);

            $quoteRequestLine->setSetUpPrice($quoteRequestLine->getProduct()->getSetUpPrice());
            $quoteRequestLine->setRentalUnitPrice($quoteRequestLine->getProduct()->getRentalUnitPrice());
            $quoteRequestLine->setTransportUnitPrice($quoteRequestLine->getProduct()->getTransportUnitPrice());
            $quoteRequestLine->setTreatmentUnitPrice($quoteRequestLine->getProduct()->getTreatmentUnitPrice());
            $quoteRequestLine->setTraceabilityUnitPrice($quoteRequestLine->getProduct()->getTraceabilityUnitPrice());
            $quoteRequestLine->setProductName($quoteRequestLine->getProduct()->getId());

            // Si codePostal, on récupère tous les coefs de celui-ci et on les affecte au quoteRequestLine
            if ($quoteRequest->getPostalCode()) {
                $quoteRequestLine->setSetUpRate($quoteRequest->getPostalCode()->getSetUpRate());
                $quoteRequestLine->setRentalRate($quoteRequest->getPostalCode()->getRentalRate());
                $quoteRequestLine->setTransportRate($quoteRequest->getPostalCode()->getTransportRate());
                $quoteRequestLine->setTreatmentRate($quoteRequest->getPostalCode()->getTreatmentRate());
                $quoteRequestLine->setTraceabilityRate($quoteRequest->getPostalCode()->getTraceabilityRate());
            } else {
                
                // Si pas de code postal, on met tous les coefs à 1 par défaut
                $quoteRequestLine->setSetUpRate($numberManager->normalize15(1));
                $quoteRequestLine->setRentalRate($numberManager->normalize15(1));
                $quoteRequestLine->setTransportRate($numberManager->normalize15(1));
                $quoteRequestLine->setTreatmentRate($numberManager->normalize15(1));
                $quoteRequestLine->setTraceabilityRate($numberManager->normalize15(1));
            }

            //Si il y a une condition d'accès, on l'affecte au quoteRequestLine
            if ($quoteRequest->getAccess()) {
                $quoteRequestLine->setAccessPrice($numberManager->normalize($productManager->getAccesPrice($quoteRequest)));
            } else {
                
                //Sinon on lui met à 0 par défaut
                $quoteRequestLine->setAccessPrice(0);
            }

            $this->em->persist($quoteRequestLine);

            // On recalcule le montant total de la ligne ainsi que celui du devis complet
            $totalLine = 0 + $this->calculateTotalLine($quoteRequestLine, $numberManager, $productManager);
            $quoteRequestLine->setTotalAmount($totalLine);
            $this->em->flush();
        }

        $total = $this->calculateTotal($quoteRequest);
        $quoteRequest->setTotalAmount($total);
        $quoteRequest->setDateUpdate(new DateTime());
        $quoteRequest->setUserUpdate($user);
        if ($doFlush) {
            $this->em->flush();
        }
    }
    
    /**
     * @param QuoteRequest   $quoteRequest
     * @param NumberManager  $numberManager
     * @param ProductManager $productManager
     * @param                $productId
     * @param                $qtty
     * @param bool           $doFlush
     *
     * @throws Exception
     */
    public function addLineFromCart(
        QuoteRequest $quoteRequest,
        NumberManager $numberManager,
        ProductManager $productManager,
        $productId,
        $qtty,
        $doFlush = true
    )
    {
        try {
            $product = $productManager->get($productId);
            $quoteRequestLine = new QuoteRequestLine();

            $quoteRequestLine->setProduct($product);
            $quoteRequestLine->setQuantity($qtty);
            $this->addLine($quoteRequest, $quoteRequestLine, $numberManager, $productManager, null, $doFlush);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }


    }
    
    /**
     * @param QuoteRequest     $quoteRequest
     * @param QuoteRequestLine $quoteRequestLine
     * @param NumberManager    $numberManager
     * @param ProductManager   $productManager
     * @param                  $user
     * @param bool             $doFlush
     * @param bool             $editQuoteRequest
     *
     * @throws Exception
     */
    public function editLine(
        QuoteRequest $quoteRequest,
        QuoteRequestLine $quoteRequestLine,
        NumberManager $numberManager,
        ProductManager $productManager,
        $user,
        $doFlush = true,
        $editQuoteRequest = true
    )
    {
        $now = new DateTime();

        $totalLine = 0 + $this->calculateTotalLine($quoteRequestLine, $numberManager, $productManager);
        $quoteRequestLine->setTotalAmount($totalLine);
        $quoteRequestLine->setDateUpdate($now);

        if ($editQuoteRequest) {
            $total = $this->calculateTotal($quoteRequest);
            $quoteRequest->setTotalAmount($total);
            $quoteRequest->setDateUpdate($now);
            $quoteRequest->setUserUpdate($user);
        }

        // Si il y a une condition d'accès, on l'affecte au quoteRequestLine
        if ($quoteRequest->getAccess()) {
            $quoteRequestLine->setAccessPrice($numberManager->normalize($productManager->getAccesPrice($quoteRequest)));
        } else {
            
            // Sinon on lui met à 0 par défaut
            $quoteRequestLine->setAccessPrice(0);
        }

        if ($doFlush) {
            $this->em->flush();
        }
    }
    
    /**
     * @param QuoteRequestLine $quoteRequestLine
     * @param NumberManager    $numberManager
     * @param ProductManager   $productManager
     *
     * @return number
     * @throws Exception
     */
    public function calculateTotalLine(
        QuoteRequestLine $quoteRequestLine,
        NumberManager $numberManager,
        ProductManager $productManager
    )
    {
        return $numberManager->normalize(
            round($productManager->calculatePrice($quoteRequestLine, $numberManager))
        );
    }
    
    /**
     * @param QuoteRequest $quoteRequest
     *
     * @return int
     */
    public function calculateTotal(QuoteRequest $quoteRequest)
    {
        $totalAmount = 0;
        if ($quoteRequest->getQuoteRequestLines() && count($quoteRequest->getQuoteRequestLines())) {

            foreach ($quoteRequest->getQuoteRequestLines() as $quoteRequestLine) {
                $totalAmount += $quoteRequestLine->getTotalAmount();
            }
        }
        
        return $totalAmount;
    }
    
    /**
     * @param QuoteRequest        $quoteRequest
     * @param Swift_Mailer        $mailer
     * @param TranslatorInterface $translator
     * @param PhpEngine           $phpEngine
     * @param                     $locale
     *
     * @return bool
     * @throws Exception
     */
    public function sendConfirmRequestEmail(
        QuoteRequest $quoteRequest,
        Swift_Mailer $mailer,
        TranslatorInterface $translator,
        PhpEngine $phpEngine,
        $locale
    )
    {

        try {
            $this->get($quoteRequest);
            $rcptTo = $quoteRequest->getEmail();

            if ($rcptTo == null || $rcptTo == '') {
                return false;
            }
    
            $message = (new Swift_Message('Reisswolf E-shop :' . $translator->trans('Commercial.ConfirmEmail.Object')))
                ->setFrom($_ENV['MAILER_PAPREC_SENDER'])
                ->setTo($rcptTo)
                ->setBody(
                    $phpEngine->render(
                        'commercial/quoteRequest/emails/confirmQuoteEmail.html.twig', [
                            'quoteRequest' => $quoteRequest,
                            'locale' => $locale
                        ]
                    ),
                    'text/html'
                )
            ;

            if ($mailer->send($message)) {
                return true;
            }
            
            return false;

        } catch (ORMException $e) {
            throw new Exception('unableToSendConfirmQuoteRequest', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
    
    /**
     * @param QuoteRequest $quoteRequest
     * @param Swift_Mailer $mailer
     * @param PhpEngine    $phpEngine
     * @param              $locale
     *
     * @return bool
     * @throws Exception
     */
    public function sendNewRequestEmail(
        QuoteRequest $quoteRequest,
        Swift_Mailer $mailer,
        PhpEngine $phpEngine,
        ProductManager $productManager,
        $locale
    )
    {
        try {
            $this->get($quoteRequest);

            /**
             * Si la quoteRequest est associé à un commercial, on lui envoie le mail d'information de la création d'une nouvelle demande
             * Sinon,
             *     si la demande est multisite alors on envoie au mail générique des demandes multisites
             *     sinon on envoie au mail générique de la région associée au code postal de la demande
             */
            if ($quoteRequest->getUserInCharge() !== null) {
                $rcptTo = $quoteRequest->getUserInCharge()->getEmail();
            } else {
                if ($quoteRequest->getIsMultisite()) {
                    $rcptTo = $_ENV['REISSWOLF_SALESMAN_MULTISITE_EMAIL'];
                } else {
                    $rcptTo = $quoteRequest->getPostalCode()->getRegion()->getEmail();
                }
            }
            
//            $rcptTo = $quoteRequest->getUserInCharge() !== null ? $quoteRequest->getUserInCharge()->getEmail() :
//                (($quoteRequest->getIsMultisite()) ? $this->container->getParameter('reisswolf_salesman_multisite_email') : $quoteRequest->getPostalCode()->getRegion()->getEmail());

            if ($rcptTo == null || $rcptTo == '') {
                return false;
            }

            $pdfFilename = date('Y-m-d') . '-Reisswolf-Devis-' . $quoteRequest->getNumber() . '.pdf';
            $pdfFile = $this->generatePDF($quoteRequest, $phpEngine, $productManager, $locale);
    
            if (!$pdfFile) {
                return false;
            }
    
            $message = (new Swift_Message('Reisswolf E-shop : Nouvelle demande de devis' . ' N°' . $quoteRequest->getId()))
                ->setFrom($_ENV['MAILER_PAPREC_SENDER'])
                ->setTo($rcptTo)
                ->setBody(
                    $phpEngine->render(
                        'commercial/quoteRequest/emails/newQuoteEmail.html.twig', [
                            'quoteRequest' => $quoteRequest,
                            'locale' => $locale
                        ]
                    ),
                    'text/html'
                )
                ->attach(Swift_Attachment::fromPath('/data/tmp/' . $pdfFilename, 'application/pdf'))
            ;

            if ($mailer->send($message)) {
                if (file_exists($pdfFile)) {
                    unlink($pdfFile);
                }

                return true;
            }
            
            return false;
            
        } catch (ORMException $e) {
            throw new Exception('unableToSendNewQuoteRequest', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
    
    /**
     * @return int
     */
    public function generateNumber()
    {
        return time();
    }
    
    /**
     * @param QuoteRequest $quoteRequest
     * @param Swift_Mailer $mailer
     * @param PhpEngine    $phpEngine
     * @param              $locale
     *
     * @return bool
     * @throws Exception
     */
    public function sendGeneratedQuoteEmail(
        QuoteRequest $quoteRequest,
        Swift_Mailer $mailer,
        PhpEngine $phpEngine,
        $locale
    )
    {
        try {
            
            $from = $this->container->getParameter('paprec_email_sender');

            $rcptTo = $quoteRequest->getEmail();

            if ($rcptTo == null || $rcptTo == '') {
                return false;
            }

            $pdfFilename = date('Y-m-d') . '-Reisswolf-Devis-' . $quoteRequest->getNumber() . '.pdf';

            $pdfFile = $this->generatePDF($quoteRequest, $locale);

            if (!$pdfFile) {
                return false;
            }
    
            $message = (new Swift_Message('Reisswolf : Votre devis'))
                ->setFrom($_ENV['MAILER_PAPREC_SENDER'])
                ->setTo($rcptTo)
                ->setBody(
                    $phpEngine->render(
                        'commercial/quoteRequest/emails/generatedQuoteEmail.html.twig', [
                            'quoteRequest' => $quoteRequest,
                            'locale' => $locale
                        ]
                    ),
                    'text/html'
                )
                ->attach(Swift_Attachment::fromPath('/data/tmp/' . $pdfFilename, 'application/pdf'))
            ;
            
            if ($mailer->send($message)) {
                if (file_exists($pdfFile)) {
                    unlink($pdfFile);
                }
                
                return true;
            }
            
            return false;

        } catch (ORMException $e) {
            throw new Exception('unableToSendGeneratedQuote', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
    
    /**
     * @param QuoteRequest $quoteRequest
     * @param              $locale
     *
     * @return bool|string
     * @throws Exception
     */
    public function generatePDF(
        QuoteRequest $quoteRequest,
        PhpEngine $phpEngine,
        ProductManager $productManager,
        $locale
    )
    {
        try {
            $pdfTmpFolder = $_ENV['TMP_FOLDER'];

            if (!is_dir($pdfTmpFolder)) {
                mkdir($pdfTmpFolder, 0755, true);
            }

            $filenameOffer = $pdfTmpFolder . '/' . md5(uniqid()) . '.pdf';
            $filename = $pdfTmpFolder . '/' . md5(uniqid()) . '.pdf';

            $today = new DateTime();

            $snappy = new Pdf($this->container->getParameter('wkhtmltopdf_path'));
            $snappy->setOption('javascript-delay', 3000);
            $snappy->setOption('dpi', 72);
//            $snappy->setOption('footer-html', $this->container->get('templating')->render('@PaprecCommercial/QuoteRequest/PDF/fr/_footer.html.twig'));

            if ($quoteRequest->getPostalCode() && $quoteRequest->getPostalCode()->getRegion()) {
                $templateDir = '/templates/commercial/quoteRequest/pdf/';
                switch (strtolower($quoteRequest->getPostalCode()->getRegion())) {
                    case 'basel':
                        $templateDir .= 'basel';
                        break;
                    case 'geneve':
                        $templateDir .= 'geneve';
                        break;
                    case 'zurich':
                    case 'zuerich':
                        $templateDir .= 'zuerich';
                        break;
                    case 'luzern':
                        $templateDir .= 'luzern';
                        break;
                }
            }

            if (!isset($templateDir) || !$templateDir || $templateDir === null) {
                return false;
            }

            // On génère la page d'offre
            $snappy->generateFromHtml([
                $phpEngine->render(
                    $templateDir . '/printQuoteOffer.html.twig', [
                        'quoteRequest' => $quoteRequest,
                        'date' => $today,
                        'locale' => $locale
                    ]
                )
            ], $filenameOffer);

            $products = $productManager->getAvailableProducts();

            // On génère la page d'offre
            $snappy->generateFromHtml([
                $phpEngine->render(
                    $templateDir . '/printQuoteContract.html.twig', [
                        'quoteRequest' => $quoteRequest,
                        'date' => $today,
                        'products' => $products
                    ]
                )
            ], $filename);
            
            // Concaténation des notices
            $pdfArray = [];
            $pdfArray[] = $filenameOffer;
            $pdfArray[] = $filename;
            
            if (count($pdfArray)) {
                $merger = new Merger();
                $merger->addIterator($pdfArray);
                file_put_contents($filename, $merger->merge());
            }

            if (file_exists($filenameOffer)) {
                unlink($filenameOffer);
            }
            
            if (!file_exists($filename)) {
                return false;
            }

            return $filename;

        } catch (ORMException $e) {
            throw new Exception('unableToGenerateProductQuote', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
