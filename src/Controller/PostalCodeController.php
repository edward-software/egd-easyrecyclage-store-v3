<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\PostalCode;
use App\Entity\User;
use App\Form\PostalCodeType;
use App\Repository\PostalCodeRepository;
use App\Service\NumberManager;
use App\Service\PostalCodeManager;
use App\Tools\DataTable;
use DateTime;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class PostalCodeController
 *
 * @package Paprec\CatalogBundle\Controller
 */
class PostalCodeController extends AbstractController
{
    /**
     * @Route("/postalCode", name="paprec_catalog_postalCode_index")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('catalog/postalCode/index.html.twig');
    }
    
    /**
     * @Route("/postalCode/loadList", name="paprec_catalog_postalCode_loadList")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request            $request
     * @param DataTable          $dataTable
     * @param PaginatorInterface $paginator
     *
     * @return JsonResponse
     */
    public function loadListAction(Request $request, DataTable $dataTable, PaginatorInterface $paginator)
    {
        $return = [];

        $filters = $request->get('filters');
        $pageSize = $request->get('length');
        $start = $request->get('start');
        $orders = $request->get('order');
        $search = $request->get('search');
        $columns = $request->get('columns');

        $cols['id'] = ['label' => 'id', 'id' => 'pC.id', 'method' => ['getId']];
        $cols['code'] = ['label' => 'code', 'id' => 'pC.code', 'method' => ['getCode']];
        $cols['city'] = ['label' => 'city', 'id' => 'pC.city', 'method' => ['getCity']];
        $cols['zone'] = ['label' => 'zone', 'id' => 'pC.zone', 'method' => ['getZone']];
        $cols['region'] = ['label' => 'region', 'id' => 'r.name', 'method' => ['getRegion', 'getName']];

        $em = $this->getDoctrine()->getManager();
        
        /** @var PostalCodeRepository $postalCodeRepository */
        $postalCodeRepository = $em->getRepository(PostalCode::class);
        
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $postalCodeRepository->createQueryBuilder('pC');
        
        $queryBuilder->select(['pC'])
            ->leftJoin('pC.region', 'r')
            ->where('pC.deleted IS NULL');

        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) === '#') {
                $queryBuilder
                    ->andWhere(
                        $queryBuilder
                            ->expr()
                            ->orx(
                                $queryBuilder
                                    ->expr()
                                    ->eq('pC.id', '?1')
                            )
                    )
                    ->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder
                    ->andWhere(
                        $queryBuilder
                            ->expr()
                            ->orx(
                                $queryBuilder->expr()->like('pC.code', '?1'),
                                $queryBuilder->expr()->like('pC.zone', '?1'),
                                $queryBuilder->expr()->like('pC.city', '?1')
                            )
                    )
                    ->setParameter(1, '%' . $search['value'] . '%');
            }
        }
    
        $dt = $dataTable->generateTable($queryBuilder, $paginator, $cols, $pageSize, $start, $orders, $columns, $filters);
        
        $return['recordsTotal'] = $dt['recordsTotal'];
        $return['recordsFiltered'] = $dt['recordsTotal'];
        $return['data'] = $dt['data'];
        $return['resultCode'] = 1;
        $return['resultDescription'] = "success";

        return new JsonResponse($return);
    }
    
    /**
     * @Route("/postalCode/export", name="paprec_catalog_postalCode_export")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function exportAction(Request $request, Spreadsheet $spreadsheet, NumberManager $numberManager)
    {
        $em = $this->getDoctrine()->getManager();
        
        /** @var PostalCodeRepository $postalCodeRepository */
        $postalCodeRepository = $em->getRepository(PostalCode::class);
        
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $postalCodeRepository->createQueryBuilder('pC');

        $queryBuilder->select(['pC'])
            ->where('pC.deleted IS NULL');

        /** @var PostalCode[] $postalCodes */
        $postalCodes = $queryBuilder->getQuery()->getResult();
    
        $spreadsheet->getProperties()->setCreator("Reisswolf Shop")
            ->setLastModifiedBy("Reisswolf Shop")
            ->setTitle("Reisswolf Shop - Postal codes")
            ->setSubject("Extract");
    
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Code')
            ->setCellValue('C1', 'Commune')
            ->setCellValue('D1', 'Tariff zone')
            ->setCellValue('E1', 'Setup rate')
            ->setCellValue('F1', 'Rental rate')
            ->setCellValue('G1', 'Transport rate')
            ->setCellValue('H1', 'Treatment rate')
            ->setCellValue('I1', 'Treacability rate')
            ->setCellValue('J1', 'Salesman in charge')
            ->setCellValue('K1', 'Region');
    
        $spreadsheet->getActiveSheet()->setTitle('Postal codes');
        $spreadsheet->setActiveSheetIndex(0);

        $i = 2;
        foreach ($postalCodes as $postalCode) {
    
            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $postalCode->getId())
                ->setCellValue('B' . $i, $postalCode->getCode())
                ->setCellValue('C' . $i, $postalCode->getCity())
                ->setCellValue('D' . $i, $postalCode->getZone())
                ->setCellValue('E' . $i, $numberManager->denormalize15($postalCode->getSetUpRate()))
                ->setCellValue('F' . $i, $numberManager->denormalize15($postalCode->getRentalRate()))
                ->setCellValue('G' . $i, $numberManager->denormalize15($postalCode->getTransportRate()))
                ->setCellValue('H' . $i, $numberManager->denormalize15($postalCode->getTreatmentRate()))
                ->setCellValue('I' . $i, $numberManager->denormalize15($postalCode->getTraceabilityRate()))
                ->setCellValue('J' . $i, ($postalCode->getUserInCharge()) ? $postalCode->getUserInCharge()->getUsername() : '')
                ->setCellValue('K' . $i, ($postalCode->getRegion()) ? $postalCode->getRegion()->getName() : '');
            $i++;
        }
    
        $writer = new Xlsx($spreadsheet);
    
        $fileName = 'ReisswolfShop-Extract-Postal-Codes-' . date('Y-m-d') . '.xlsx';
    
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
     * @Route("/postalCode/view/{id}", name="paprec_catalog_postalCode_view")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request           $request
     * @param PostalCode        $postalCode
     * @param PostalCodeManager $postalCodeManager
     *
     * @return Response
     * @throws EntityNotFoundException
     */
    public function viewAction(Request $request, PostalCode $postalCode, PostalCodeManager $postalCodeManager)
    {
        $postalCodeManager->isDeleted($postalCode, true);

        return $this->render('catalog/postalCode/view.html.twig', [
            'postalCode' => $postalCode
        ]);
    }
    
    /**
     * @Route("/postalCode/add", name="paprec_catalog_postalCode_add")
     * @Security("has_role('ROLE_COMMERCIAL')")
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

        $postalCode = new PostalCode();
        
        $form = $this->createForm(PostalCodeType::class, $postalCode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $postalCode = $form->getData();
            $postalCode->setSetUpRate($numberManager->normalize15($postalCode->getSetUpRate()));
            $postalCode->setRentalRate($numberManager->normalize15($postalCode->getRentalRate()));
            $postalCode->setTransportRate($numberManager->normalize15($postalCode->getTransportRate()));
            $postalCode->setTreatmentRate($numberManager->normalize15($postalCode->getTreatmentRate()));
            $postalCode->setTraceabilityRate($numberManager->normalize15($postalCode->getTraceabilityRate()));
            $postalCode->setDateCreation(new DateTime);
            $postalCode->setUserCreation($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($postalCode);
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_postalCode_view', [
                'id' => $postalCode->getId()
            ]);
        }

        return $this->render('catalog/postalCode/add.html.twig', [
            'form' => $form->createView()
        ]);
    }
    
    /**
     * @Route("/postalCode/edit/{id}", name="paprec_catalog_postalCode_edit")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request           $request
     * @param PostalCode        $postalCode
     * @param NumberManager     $numberManager
     * @param PostalCodeManager $postalCodeManager
     *
     * @return RedirectResponse|Response
     * @throws EntityNotFoundException
     */
    public function editAction(Request $request, PostalCode $postalCode, NumberManager $numberManager, PostalCodeManager $postalCodeManager)
    {
        /** @var User $user */
        $user = $this->getUser();

        $postalCodeManager->isDeleted($postalCode, true);
        
        $postalCode->setSetUpRate($numberManager->denormalize15($postalCode->getSetUpRate()));
        $postalCode->setRentalRate($numberManager->denormalize15($postalCode->getRentalRate()));
        $postalCode->setTransportRate($numberManager->denormalize15($postalCode->getTransportRate()));
        $postalCode->setTreatmentRate($numberManager->denormalize15($postalCode->getTreatmentRate()));
        $postalCode->setTraceabilityRate($numberManager->denormalize15($postalCode->getTraceabilityRate()));

        $form = $this->createForm(PostalCodeType::class, $postalCode);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $postalCode = $form->getData();

            $postalCode->setSetUpRate($numberManager->normalize15($postalCode->getSetUpRate()));
            $postalCode->setRentalRate($numberManager->normalize15($postalCode->getRentalRate()));
            $postalCode->setTransportRate($numberManager->normalize15($postalCode->getTransportRate()));
            $postalCode->setTreatmentRate($numberManager->normalize15($postalCode->getTreatmentRate()));
            $postalCode->setTraceabilityRate($numberManager->normalize15($postalCode->getTraceabilityRate()));

            $postalCode->setDateUpdate(new DateTime);
            $postalCode->setUserUpdate($user);

            $em = $this->getDoctrine()->getManager();
            $em->flush();
            
            return $this->redirectToRoute('paprec_catalog_postalCode_view', [
                'id' => $postalCode->getId()
            ]);
        }
        
        return $this->render('catalog/postalCode/edit.html.twig', [
            'form' => $form->createView(),
            'postalCode' => $postalCode
        ]);
    }
    
    /**
     * @Route("/postalCode/remove/{id}", name="paprec_catalog_postalCode_remove")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request    $request
     * @param PostalCode $postalCode
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function removeAction(Request $request, PostalCode $postalCode)
    {
        $em = $this->getDoctrine()->getManager();

        $postalCode->setDeleted(new DateTime());
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_postalCode_index');
    }
    
    /**
     * @Route("/postalCode/removeMany/{ids}", name="paprec_catalog_postalCode_removeMany")
     * @Security("has_role('ROLE_COMMERCIAL')")
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
            
            /** @var PostalCode[] $postalCodes */
            $postalCodes = $em->getRepository(PostalCode::class)->findById($ids);
            
            foreach ($postalCodes as $postalCode) {
                $postalCode->setDeleted(new DateTime);
            }
            
            $em->flush();
        }

        return $this->redirectToRoute('paprec_catalog_postalCode_index');
    }
    
    /**
     * @Route("/postalCode/autocomplete", name="paprec_catalog_postalCode_autocomplete")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function autocompleteAction(Request $request)
    {
        $codes = [];
        $term = trim(strip_tags($request->get('term')));

        $em = $this->getDoctrine()->getManager();
        
        /** @var PostalCodeRepository $postalCodeRepository */
        $postalCodeRepository = $em->getRepository(PostalCode::class);

        /** @var PostalCode[] $postalCodes */
        $postalCodes = $postalCodeRepository
            ->createQueryBuilder('pC')
            ->where('pC.code LIKE :code')
            ->andWhere('pC.deleted is NULL')
            ->setParameter('code', '%' . $term . '%')
            ->getQuery()
            ->getResult();
        
        foreach ($postalCodes as $postalCode) {
            $codes[] = $postalCode->getCode();
        }

        $response = new JsonResponse();
        $response->setData($codes);

        return $response;
    }
}
