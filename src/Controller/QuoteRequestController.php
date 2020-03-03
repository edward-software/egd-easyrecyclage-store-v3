<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\QuoteRequest;
use App\Entity\QuoteRequestLine;
use App\Entity\User;
use App\Form\QuoteRequestLineAddType;
use App\Form\QuoteRequestLineEditType;
use App\Form\QuoteRequestType;
use App\Service\NumberManager;
use App\Service\ProductManager;
use App\Service\QuoteRequestManager;
use App\Tools\DataTable;
use DateTime;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Exception as PSException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mime\FileinfoMimeTypeGuesser;
use Symfony\Component\Mime\MimeTypeGuesserInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

class QuoteRequestController extends AbstractController
{
    /**
     * @Route("/quoteRequest", name="paprec_commercial_quoteRequest_index")
     * @Security("has_role('ROLE_COMMERCIAL') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('commercial/quoteRequest/index.html.twig');
    }
    
    /**
     * @Route("/quoteRequest/loadList", name="paprec_commercial_quoteRequest_loadList")
     * @Security("has_role('ROLE_COMMERCIAL') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     *
     * @param Request            $request
     * @param NumberManager      $numberManager
     * @param DataTable          $dataTable
     * @param PaginatorInterface $paginator
     *
     * @return JsonResponse
     */
    public function loadListAction(
        Request $request,
        NumberManager $numberManager,
        DataTable $dataTable,
        PaginatorInterface $paginator,
        TranslatorInterface $translator
    )
    {
        $return = [];

        $filters = $request->get('filters');
        $pageSize = $request->get('length');
        $start = $request->get('start');
        $orders = $request->get('order');
        $search = $request->get('search');
        $columns = $request->get('columns');

        $cols['id'] = ['label' => 'id', 'id' => 'q.id', 'method' => ['getId']];
        $cols['businessName'] = ['label' => 'businessName', 'id' => 'q.business_name', 'method' => ['getBusinessName']];
        $cols['totalAmount'] = ['label' => 'totalAmount', 'id' => 'q.total_amount', 'method' => ['getTotalAmount']];
        $cols['quoteStatus'] = ['label' => 'quoteStatus', 'id' => 'q.quote_status', 'method' => ['getQuoteStatus']];
        $cols['dateCreation'] = [
            'label' => 'dateCreation', 'id' => 'q.date_creation', 'method' => ['getDateCreation'], 'filter' => [[
                'name' => 'format', 'args' => ['Y-m-d H:i:s']]
            ]
        ];
        
        $em = $this->getDoctrine()->getManager();
        
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $em->createQueryBuilder();
        
        $queryBuilder
            ->select(['q'])
            ->from(QuoteRequest::class, 'q')
            ->where('q.deleted IS NULL');

        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) === '#') {
                $queryBuilder
                    ->andWhere(
                        $queryBuilder
                            ->expr()
                            ->orx(
                                $queryBuilder
                                    ->expr()
                                    ->eq('q.id', '?1')
                                )
                    )
                    ->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder
                    ->andWhere(
                        $queryBuilder
                            ->expr()
                            ->orx(
                                $queryBuilder->expr()->like('q.business_nName', '?1'),
                                $queryBuilder->expr()->like('q.total_amount', '?1'),
                                $queryBuilder->expr()->like('q.quote_status', '?1'),
                                $queryBuilder->expr()->like('q.date_creation', '?1')
                            )
                    )
                    ->setParameter(1, '%' . $search['value'] . '%');
            }
        }
    
        $dt = $dataTable->generateTable($queryBuilder, $paginator, $cols, $pageSize, $start, $orders, $columns, $filters);
        
        // Reformatage de certaines données
        $tmp = [];
        foreach ($dt['data'] as $data) {
            $line = $data;
            $line['totalAmount'] = $numberManager->formatAmount($data['totalAmount'], 'EUR', $request->getLocale());
            $line['quoteStatus'] = $translator->trans("Commercial.QuoteStatusList." . $data['quoteStatus']);
            $tmp[] = $line;
        }
    
        $dt['data'] = $tmp;

        $return['recordsTotal'] = $dt['recordsTotal'];
        $return['recordsFiltered'] = $dt['recordsTotal'];
        $return['data'] = $dt['data'];
        $return['resultCode'] = 1;
        $return['resultDescription'] = "success";

        return new JsonResponse($return);
    }
    
    /**
     * @Route("/quoteRequest/export/{status}/{dateStart}/{dateEnd}", defaults={"status"=null, "dateStart"=null, "dateEnd"=null}, name="paprec_commercial_quoteRequest_export")
     * @Security("has_role('ROLE_COMMERCIAL') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     *
     * @param Request       $request
     * @param               $dateStart
     * @param               $dateEnd
     * @param               $status
     * @param NumberManager $numberManager
     * @param Spreadsheet   $spreadsheet
     *
     * @return mixed
     * @throws PSException
     */
    public function exportAction(
        Request $request,
        $dateStart,
        $dateEnd,
        $status,
        NumberManager $numberManager,
        Spreadsheet $spreadsheet,
        TranslatorInterface $translator
    )
    {
        $em = $this->getDoctrine()->getManager();

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $em->createQueryBuilder();
        
        $queryBuilder
            ->select(['q'])
            ->from(QuoteRequest::class , 'q')
            ->where('q.deleted IS NULL');
        
        if ($status != null && !empty($status)) {
            $queryBuilder
                ->andWhere('q.quote_status = :status')
                ->setParameter('status', $status);
        }
        
        if ($dateStart != null && $dateEnd != null && !empty($dateStart) && !empty($dateEnd)) {
            $queryBuilder
                ->andWhere('q.date_creation BETWEEN :dateStart AND :dateEnd')
                ->setParameter('dateStart', $dateStart)
                ->setParameter('dateEnd', $dateEnd);
        }

        /** @var QuoteRequest[] $quoteRequests */
        $quoteRequests = $queryBuilder->getQuery()->getResult();
    
        $spreadsheet
            ->getProperties()
            ->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Reisswolf Shop")
            ->setTitle("Paprec Easy Recyclage - Devis")
            ->setSubject("Extraction");
    
        $sheet = $spreadsheet->setActiveSheetIndex(0);
        $sheet->setTitle('Devis');
        
        // Labels
        $sheetLabels = [
            'ID',
            'Creation date',
            'Update date',
            'Deleted',
            'User creation',
            'User update',
            'Locale',
            'Number',
            'Canton',
            'Business name',
            'Civility',
            'Last name',
            'First name',
            'Email',
            'Phone',
            'is Multisite',
            'Staff',
            'Access',
            'Address',
            'City',
            'Customer comment',
            'Status',
            'Total amount',
            'Overall Discount',
            'Salesman Comment',
            'Annual Budget',
            'Frequency',
            'Frequency Times',
            'Frequency Interval',
            'Customer ID',
            'Reference',
            'User in charge',
            'Postal Code',
        ];
    
        $xAxe = 'A';
        foreach ($sheetLabels as $label) {
            $sheet->setCellValue($xAxe . 1, $label);
            $xAxe++;
        }
        
        $yAxe = 2;
        foreach ($quoteRequests as $quoteRequest) {
            
            $getters = [
                $quoteRequest->getId(),
                $quoteRequest->getDateCreation()->format('Y-m-d'),
                $quoteRequest->getDateUpdate() ? $quoteRequest->getDateUpdate()->format('Y-m-d') : '',
                $quoteRequest->getDeleted() ? 'true' : 'false',
                $quoteRequest->getUserCreation(),
                $quoteRequest->getUserUpdate(),
                $quoteRequest->getLocale(),
                $numberManager->denormalize($quoteRequest->getNumber()),
                $quoteRequest->getCanton(),
                $quoteRequest->getBusinessName(),
                $quoteRequest->getCivility(),
                $quoteRequest->getLastName(),
                $quoteRequest->getFirstName(),
                $quoteRequest->getEmail(),
                $quoteRequest->getPhone(),
                $quoteRequest->getIsMultisite() ? 'true' : 'false',
                $translator->trans('Commercial.StaffList.' . $quoteRequest->getStaff()),
                $quoteRequest->getAccess(),
                $quoteRequest->getAddress(),
                $quoteRequest->getCity(),
                $quoteRequest->getComment(),
                $quoteRequest->getQuoteStatus(),
                $quoteRequest->getTotalAmount(),
                $quoteRequest->getOverallDiscount(),
                $quoteRequest->getSalesmanComment(),
                $numberManager->denormalize($quoteRequest->getAnnualBudget()),
                $quoteRequest->getFrequency(),
                $quoteRequest->getFrequencyTimes(),
                $quoteRequest->getFrequencyInterval(),
                $quoteRequest->getCustomerId(),
                $quoteRequest->getReference(),
                $quoteRequest->getUserInCharge() ? $quoteRequest->getUserInCharge()->getFirstName() . " " . $quoteRequest->getUserInCharge()->getLastName() : '',
                $quoteRequest->getPostalCode() ? $quoteRequest->getPostalCode()->getCode() : '',
            ];
    
            $xAxe = 'A';
            foreach ($getters as $getter) {
                $sheet->setCellValue($xAxe . $yAxe, (string) $getter);
                $xAxe++;
            }
            $yAxe++;
        }
    
        // Format
        $sheet->getStyle(
            "A1:" . $sheet->getHighestDataColumn() . 1)->getAlignment()->setHorizontal('center');
        $sheet->getStyle(
            "A2:" . $sheet->getHighestDataColumn() . $sheet->getHighestDataRow())->getAlignment()->setHorizontal('left');
    
        // Resize columns
        for ($i = 'A'; $i != $sheet->getHighestDataColumn(); $i++) {
            $sheet->getColumnDimension($i)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        $fileName = 'ReisswolfShop-Extraction-Devis--' . date('Y-m-d') . '.xlsx';
    
        // Create a Response
        $response =  new StreamedResponse(
            function () use ($writer, $fileName) {
                $writer->save($fileName);
            }
        );
    
        // Adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName
        );
        
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }
    
    /**
     * @Route("/quoteRequest/view/{id}", name="paprec_commercial_quoteRequest_view")
     * @Security("has_role('ROLE_COMMERCIAL') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     *
     * @param Request             $request
     * @param QuoteRequest        $quoteRequest
     * @param QuoteRequestManager $quoteRequestManager
     *
     * @return Response
     * @throws EntityNotFoundException
     */
    public function viewAction(Request $request, QuoteRequest $quoteRequest, QuoteRequestManager $quoteRequestManager)
    {
        $quoteRequestManager->isDeleted($quoteRequest, true);

        return $this->render('commercial/quoteRequest/view.html.twig', [
            'quoteRequest' => $quoteRequest
        ]);
    }
    
    /**
     * @Route("/quoteRequest/add", name="paprec_commercial_quoteRequest_add")
     * @Security("has_role('ROLE_COMMERCIAL') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function addAction(Request $request, NumberManager $numberManager)
    {
        /** @var User $user */
        $user = $this->getUser();

        $status = [];
        foreach ($this->getParameter('paprec_quote_status') as $s) {
            $status[$s] = $s;
        }

        $locales = [];
        foreach ($this->getParameter('paprec_languages') as $language) {
            $locales[$language] = strtolower($language);
        }

        $access = [];
        foreach ($this->getParameter('paprec_quote_access') as $a) {
            $access[$a] = $a;
        }

        $staff = [];
        foreach ($this->getParameter('paprec_quote_staff') as $s) {
            $staff[$s] = $s;
        }
    
        /** @var QuoteRequest $quoteRequest */
        $quoteRequest = new QuoteRequest();
        
        $form = $this->createForm(QuoteRequestType::class, $quoteRequest, [
            'status' => $status,
            'locales' => $locales,
            'access' => $access,
            'staff' => $staff
        ]);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $quoteRequest = $form->getData();
            
            $quoteRequest->setOverallDiscount($numberManager->normalize($quoteRequest->getOverallDiscount()));
            $quoteRequest->setAnnualBudget($numberManager->normalize($quoteRequest->getAnnualBudget()));
            $quoteRequest->setUserCreation($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($quoteRequest);
            $em->flush();

            return $this->redirectToRoute('paprec_commercial_quoteRequest_view', [
                'id' => $quoteRequest->getId()
            ]);
        }

        return $this->render('commercial/quoteRequest/add.html.twig', [
            'form' => $form->createView()
        ]);
    }
    
    /**
     * @Route("/quoteRequest/edit/{id}", name="paprec_commercial_quoteRequest_edit")
     * @Security("has_role('ROLE_COMMERCIAL') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     *
     * @param Request             $request
     * @param QuoteRequest        $quoteRequest
     * @param NumberManager       $numberManager
     * @param QuoteRequestManager $quoteRequestManager
     *
     * @return RedirectResponse|Response
     * @throws EntityNotFoundException
     */
    public function editAction(
        Request $request,
        QuoteRequest $quoteRequest,
        NumberManager $numberManager,
        QuoteRequestManager $quoteRequestManager
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $quoteRequestManager->isDeleted($quoteRequest, true);

        $status = [];
        foreach ($this->getParameter('paprec_quote_status') as $s) {
            $status[$s] = $s;
        }

        $locales = [];
        foreach ($this->getParameter('paprec_languages') as $language) {
            $locales[$language] = strtolower($language);
        }

        $access = [];
        foreach ($this->getParameter('paprec_quote_access') as $a) {
            $access[$a] = $a;
        }

        $staff = [];
        foreach ($this->getParameter('paprec_quote_staff') as $s) {
            $staff[$s] = $s;
        }

        $quoteRequest->setOverallDiscount($numberManager->denormalize($quoteRequest->getOverallDiscount()));
        $quoteRequest->setAnnualBudget($numberManager->denormalize($quoteRequest->getAnnualBudget()));

        $form = $this->createForm(QuoteRequestType::class, $quoteRequest, [
            'status' => $status,
            'locales' => $locales,
            'access' => $access,
            'staff' => $staff
        ]);

        $savedCommercial = $quoteRequest->getUserInCharge();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quoteRequest = $form->getData();
            $quoteRequest->setOverallDiscount($numberManager->normalize($quoteRequest->getOverallDiscount()));
            $quoteRequest->setAnnualBudget($numberManager->normalize($quoteRequest->getAnnualBudget()));

            if ($quoteRequest->getQuoteRequestLines()) {
                foreach ($quoteRequest->getQuoteRequestLines() as $line) {
                    $quoteRequestManager->editLine($quoteRequest, $line, $user, false, false);
                }
            }
            $quoteRequest->setTotalAmount($quoteRequestManager->calculateTotal($quoteRequest));

            $quoteRequest->setDateUpdate(new DateTime());
            $quoteRequest->setUserUpdate($user);

            // Si le commercial en charge a changé, alors on envoie un mail au nouveau commercial
            if ((!$savedCommercial && $quoteRequest->getUserInCharge())
                || ($savedCommercial && $savedCommercial->getId() !== $quoteRequest->getUserInCharge()->getId())) {
                $quoteRequestManager->sendNewRequestEmail($quoteRequest, $quoteRequest->getUserInCharge()->getLang());
                $this->get('session')->getFlashBag()->add('success', 'newUserInChargeWarned');
            }
            
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_commercial_quoteRequest_view', [
                'id' => $quoteRequest->getId()
            ]);
        }

        return $this->render('commercial/quoteRequest/edit.html.twig', [
            'form' => $form->createView(),
            'quoteRequest' => $quoteRequest
        ]);
    }
    
    /**
     * @Route("/quoteRequest/remove/{id}", name="paprec_commercial_quoteRequest_remove")
     * @Security("has_role('ROLE_COMMERCIAL') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     *
     * @param Request      $request
     * @param QuoteRequest $quoteRequest
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function removeAction(Request $request, QuoteRequest $quoteRequest)
    {
        $em = $this->getDoctrine()->getManager();

        $quoteRequest->setDeleted(new DateTime());
        $em->flush();

        return $this->redirectToRoute('paprec_commercial_quoteRequest_index');
    }
    
    /**
     * @Route("/quoteRequest/removeMany/{ids}", name="paprec_commercial_quoteRequest_removeMany")
     * @Security("has_role('ROLE_COMMERCIAL') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     *
     * @param Request $request
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function removeManyAction(Request $request)
    {
        $ids = $request->get('ids');

        if (!$ids) {
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        $ids = explode(',', $ids);

        if (is_array($ids) && count($ids)) {
            
            /** @var QuoteRequest[] $quoteRequests */
            $quoteRequests = $em->getRepository(QuoteRequest::class)->findById($ids);
            
            foreach ($quoteRequests as $quoteRequest) {
                $quoteRequest->setDeleted(new DateTime);
            }
            
            $em->flush();
        }

        return $this->redirectToRoute('paprec_commercial_quoteRequest_index');
    }
    
    /**
     * @Route("/quoteRequest/{id}/addLine", name="paprec_commercial_quoteRequest_addLine")
     * @Security("has_role('ROLE_COMMERCIAL') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     *
     * @param Request             $request
     * @param QuoteRequest        $quoteRequest
     * @param QuoteRequestManager $quoteRequestManager
     * @param NumberManager       $numberManager
     * @param ProductManager      $productManager
     *
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function addLineAction(
        Request $request,
        QuoteRequest $quoteRequest,
        QuoteRequestManager $quoteRequestManager,
        NumberManager $numberManager,
        ProductManager $productManager
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($quoteRequest->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        $quoteRequestLine = new QuoteRequestLine();

        $form = $this->createForm(QuoteRequestLineAddType::class, $quoteRequestLine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quoteRequestLine = $form->getData();
            $quoteRequestManager->addLine($quoteRequest, $quoteRequestLine, $numberManager, $productManager, $user);

            return $this->redirectToRoute('paprec_commercial_quoteRequest_view', [
                'id' => $quoteRequest->getId()
            ]);
        }

        return $this->render('commercial/quoteRequestLine/add.html.twig', [
            'form' => $form->createView(),
            'quoteRequest' => $quoteRequest,
        ]);
    }
    
    /**
     * @Route("/quoteRequest/{id}/editLine/{quoteLineId}", name="paprec_commercial_quoteRequest_editLine")
     * @Security("has_role('ROLE_COMMERCIAL') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     *
     * @ParamConverter("quoteRequest", options={"id" = "id"})
     * @ParamConverter("quoteRequestLine", options={"id" = "quoteLineId"})
     *
     * @param Request             $request
     * @param QuoteRequest        $quoteRequest
     * @param QuoteRequestLine    $quoteRequestLine
     * @param QuoteRequestManager $quoteRequestManager
     * @param NumberManager       $numberManager
     * @param ProductManager      $productManager
     *
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function editLineAction(
        Request $request,
        QuoteRequest $quoteRequest,
        QuoteRequestLine $quoteRequestLine,
        QuoteRequestManager $quoteRequestManager,
        NumberManager $numberManager,
        ProductManager $productManager
    )
    {
        if ($quoteRequest->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        if ($quoteRequestLine->getQuoteRequest() !== $quoteRequest) {
            throw new NotFoundHttpException();
        }

        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(QuoteRequestLineEditType::class, $quoteRequestLine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quoteRequestManager->editLine($quoteRequest, $quoteRequestLine, $numberManager, $productManager, $user);

            return $this->redirectToRoute('paprec_commercial_quoteRequest_view', [
                'id' => $quoteRequest->getId()
            ]);
        }

        return $this->render('commercial/quoteRequestLine/edit.html.twig', [
            'form' => $form->createView(),
            'quoteRequest' => $quoteRequest,
            'quoteRequestLine' => $quoteRequestLine
        ]);
    }
    
    /**
     *
     * @Route("/quoteRequest/{id}/removeLine/{quoteLineId}", name="paprec_commercial_quoteRequest_removeLine")
     * @Security("has_role('ROLE_COMMERCIAL') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     *
     * @ParamConverter("quoteRequest", options={"id" = "id"})
     * @ParamConverter("quoteRequestLine", options={"id" = "quoteLineId"})
     *
     * @param Request          $request
     * @param QuoteRequest     $quoteRequest
     * @param QuoteRequestLine $quoteRequestLine
     *
     * @return RedirectResponse
     */
    public function removeLineAction(
        Request $request,
        QuoteRequest $quoteRequest,
        QuoteRequestLine $quoteRequestLine,
        QuoteRequestManager $quoteRequestManager
    )
    {
        if ($quoteRequest->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        if ($quoteRequestLine->getQuoteRequest() !== $quoteRequest) {
            throw new NotFoundHttpException();
        }
        
        $em = $this->getDoctrine()->getManager();

        $em->remove($quoteRequestLine);
        $em->flush();

        $total = $quoteRequestManager->calculateTotal($quoteRequest);
        $quoteRequest->setTotalAmount($total);
        
        $em->flush();
        
        return $this->redirectToRoute('paprec_commercial_quoteRequest_view', [
            'id' => $quoteRequest->getId()
        ]);
    }
    
    /**
     * @Route("/quoteRequest/{id}/sendGeneratedQuote", name="paprec_commercial_quoteRequest_sendGeneratedQuote")
     * @Security("has_role('ROLE_COMMERCIAL') or (has_role('ROLE_COMMERCIAL_DIVISION') and 'DI' in user.getDivisions())")
     *
     * @param QuoteRequest        $quoteRequest
     * @param QuoteRequestManager $quoteRequestManager
     *
     * @return RedirectResponse
     * @throws EntityNotFoundException
     */
    public function sendGeneratedQuoteAction(
        QuoteRequest $quoteRequest,
        QuoteRequestManager $quoteRequestManager,
        Swift_Mailer $mailer,
        ProductManager $productManager
    )
    {
        $quoteRequestManager->isDeleted($quoteRequest, true);

        if ($quoteRequest->getPostalCode() && $quoteRequest->getPostalCode()->getRegion()) {
            $sendQuote = $quoteRequestManager->sendGeneratedQuoteEmail($quoteRequest, $mailer, $productManager, $quoteRequest->getLocale());
            if ($sendQuote) {
                $this->get('session')->getFlashBag()->add('success', 'generatedQuoteSent');
            } else {
                $this->get('session')->getFlashBag()->add('error', 'generatedQuoteNotSent');
            }
        }

        return $this->redirectToRoute('paprec_commercial_quoteRequest_view', [
            'id' => $quoteRequest->getId()
        ]);
    }
    
    /**
     * @Route("/quoteRequest/{id}/downloadQuote", name="paprec_commercial_quote_request_download")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param QuoteRequest             $quoteRequest
     * @param QuoteRequestManager      $quoteRequestManager
     * @param ProductManager           $productManager
     *
     * @return BinaryFileResponse
     * @throws Exception
     */
    public function downloadAssociatedInvoiceAction(
        QuoteRequest $quoteRequest,
        QuoteRequestManager $quoteRequestManager,
        ProductManager $productManager
    )
    {
        // On commence par pdf générés (seulement ceux générés dans le BO pour éviter de supprimer un PDF en cours d'envoi pour un utilisateur
        $pdfFolder = $this->getParameter('app.data_tmp_directory');
        
        /** @var Finder $finder */
        $finder = new Finder();

        $finder->files()->in($pdfFolder);

        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $absoluteFilePath = $file->getRealPath();
                if (file_exists($absoluteFilePath)) {
                    unlink($absoluteFilePath);
                }
            }
        }

        /** @var User $user */
        $user = $this->getUser();
        
        $file = $quoteRequestManager->generatePDF($quoteRequest, $productManager, $user->getLang());
        $filename = substr($file, strrpos($file, '/') + 1);
        
        // This should return the file to the browser as response
        $response = new BinaryFileResponse($pdfFolder . '/' . $filename);
        
        /** @var FileinfoMimeTypeGuesser $fileinfoMimeTypeGuesser */
        $fileinfoMimeTypeGuesser = new FileinfoMimeTypeGuesser();

        // Set the mimetype with the guesser or manually
        if ($fileinfoMimeTypeGuesser->isGuesserSupported()) {
            // Guess the mimetype of the file according to the extension of the file
            $response->headers->set('Content-Type', $fileinfoMimeTypeGuesser->guessMimeType($pdfFolder . '/' . $filename));
        } else {
            // Set the mimetype of the file manually, in this case for a text file is text/plain
            $response->headers->set('Content-Type', 'application/pdf');
        }

        // Set content disposition inline of the file
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'Quote-' . $quoteRequest->getBusinessName() . '-' . $quoteRequest->getId() . ' .pdf'
        );
        
        return $response;
    }
}
