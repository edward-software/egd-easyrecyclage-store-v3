<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Region;
use App\Entity\User;
use App\Form\RegionType;
use App\Repository\RegionRepository;
use App\Service\RegionManager;
use App\Service\UserManager;
use DateTime;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\QueryBuilder;
use Exception;
use PhpOffice\PhpSpreadsheet\Exception as PSException;
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

class RegionController extends AbstractController
{
    /**
     * @Route("/region", name="paprec_catalog_region_index")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('catalog/region/index.html.twig');
    }
    
    /**
     * @Route("/region/loadList", name="paprec_catalog_region_loadList")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function loadListAction(Request $request)
    {
        $return = [];

        $filters = $request->get('filters');
        $pageSize = $request->get('length');
        $start = $request->get('start');
        $orders = $request->get('order');
        $search = $request->get('search');
        $columns = $request->get('columns');

        $cols['id'] = ['label' => 'id', 'id' => 'r.id', 'method' => ['getId']];
        $cols['name'] = ['label' => 'name', 'id' => 'r.name', 'method' => ['getName']];
        $cols['email'] = ['label' => 'email', 'id' => 'r.email', 'method' => ['getEmail']];

        $em = $this->getDoctrine()->getManager();
        
        /** @var RegionRepository $regionRepository */
        $regionRepository = $em->getRepository(Region::class);
        
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $regionRepository->createQueryBuilder('r');
        
        $queryBuilder
            ->select(['r'])
            ->where('r.deleted IS NULL');

        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) === '#') {
                $queryBuilder
                    ->andWhere(
                        $queryBuilder
                            ->expr()
                            ->orx(
                                $queryBuilder
                                    ->expr()
                                    ->eq('r.id', '?1')
                            )
                    )
                    ->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder
                    ->andWhere(
                        $queryBuilder
                            ->expr()
                            ->orx(
                                $queryBuilder
                                    ->expr()
                                    ->like('r.name', '?1'),
                                $queryBuilder
                                    ->expr()
                                    ->like('r.email', '?1')
                            )
                    )
                    ->setParameter(1, '%' . $search['value'] . '%');
            }
        }

        $datatable = $this->get('goondi_tools.datatable')->generateTable($cols, $queryBuilder, $pageSize, $start, $orders, $columns, $filters);

        $return['recordsTotal'] = $datatable['recordsTotal'];
        $return['recordsFiltered'] = $datatable['recordsTotal'];
        $return['data'] = $datatable['data'];
        $return['resultCode'] = 1;
        $return['resultDescription'] = "success";

        return new JsonResponse($return);
    }
    
    /**
     * @Route("/region/export", name="paprec_catalog_region_export")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request     $request
     * @param UserManager $userManager
     * @param Spreadsheet $spreadsheet
     *
     * @return StreamedResponse
     * @throws PSException
     */
    public function exportAction(Request $request, UserManager $userManager, Spreadsheet $spreadsheet)
    {
        $em = $this->getDoctrine()->getManager();
        
        /** @var RegionRepository $regionRepository */
        $regionRepository = $em->getRepository(Region::class);
        
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $regionRepository->createQueryBuilder('r');

        $queryBuilder
            ->select(['r'])
            ->where('r.deleted IS NULL');

        /** @var Region[] $regions */
        $regions = $queryBuilder->getQuery()->getResult();
    
        $spreadsheet
            ->getProperties()
            ->setCreator("Reisswolf Shop")
            ->setLastModifiedBy("Reisswolf Shop")
            ->setTitle("Reisswolf Shop - Regions")
            ->setSubject("Extract");
    
        $sheet = $spreadsheet->setActiveSheetIndex(0);
        $sheet->setTitle('Regions');
    
        $sheetLabels = [
            'R. ID',
            'R. Creation date',
            'R. Update date',
            'R. Deleted',
            'User creation',
            'User update',
            'Name',
            'Contact email',
            'U. ID',
            'U. Username',
            'U. Email',
            'U. Creation data',
            'U. Update date',
            'U. Deleted',
            'U. Company name',
            'U. Last name',
            'U. First name',
            'U. Language',
        ];
    
        $xAxe = 'A';
        foreach ($sheetLabels as $label) {
            $sheet->setCellValue($xAxe . 1, $label);
            $xAxe++;
        }
    
        $yAxe = 2;
        foreach ($regions as $region) {
            
            /** @var User $user */
            $user = $userManager->get($region->getUserCreation());
            
            // Getters
            $getters = [
                $region->getId(),
                $region->getDateCreation()->format('Y-m-d'),
                $region->getDateUpdate() ? $region->getDateUpdate()->format('Y-m-d') : '',
                $region->getDeleted() ? 'true' : 'false',
                $region->getUserCreation(),
                $region->getUserUpdate(),
                $region->getName(),
                $region->getEmail(),
                $user->getId(),
                $user->getUsername(),
                $user->getEmail(),
                $user->getDateCreation()->format('Y-m-d'),
                $user->getDateUpdate() ? $user->getDateUpdate()->format('Y-m-d') : '',
                $user->getDeleted() ? 'true' : 'false',
                $user->getCompanyName(),
                $user->getLastName(),
                $user->getFirstName(),
                $user->getLang(),
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
        for ($i = 'A'; $i <= $sheet->getHighestDataColumn(); $i++) {
            $sheet->getColumnDimension($i)->setAutoSize(true);
        }
        
        $writer = new Xlsx($spreadsheet);

        $fileName = 'ReisswolfShop-Extract-Regions-' . date('Y-m-d') . '.xlsx';
    
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
     * @Route("/region/view/{id}", name="paprec_catalog_region_view")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request       $request
     * @param Region        $region
     * @param RegionManager $regionManager
     *
     * @return Response
     * @throws EntityNotFoundException
     */
    public function viewAction(Request $request, Region $region, RegionManager $regionManager)
    {
        $regionManager->isDeleted($region, true);

        return $this->render('catalog/region/view.html.twig', [
            'region' => $region
        ]);
    }

    /**
     * @Route("/region/add", name="paprec_catalog_region_add")
     * @Security("has_role('ROLE_COMMERCIAL')")
     */
    public function addAction(Request $request, Region $region)
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(RegionType::class, $region);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $region = $form->getData();

            $region->setDateCreation(new DateTime);
            $region->setUserCreation($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($region);
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_region_view', [
                'id' => $region->getId()
            ]);
        }

        return $this->render('catalog/region/add.html.twig', [
            'form' => $form->createView()
        ]);
    }
    
    /**
     * @Route("/region/edit/{id}", name="paprec_catalog_region_edit")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request $request
     * @param Region  $region
     *
     * @return RedirectResponse|Response
     * @throws EntityNotFoundException
     */
    public function editAction(Request $request, Region $region, RegionManager $regionManager)
    {
        /** @var User $user */
        $user = $this->getUser();

        $regionManager->isDeleted($region, true);

        $form = $this->createForm(RegionType::class, $region);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $region = $form->getData();

            $region->setDateUpdate(new DateTime);
            $region->setUserUpdate($user);

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_region_view', [
                'id' => $region->getId()
            ]);
        }

        return $this->render('catalog/region/edit.html.twig', [
            'form' => $form->createView(),
            'region' => $region
        ]);
    }
    
    /**
     * @Route("/region/remove/{id}", name="paprec_catalog_region_remove")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request $request
     * @param Region  $region
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function removeAction(Request $request, Region $region)
    {
        $em = $this->getDoctrine()->getManager();

        $region->setDeleted(new DateTime());
        if ($region->getPostalCodes() && count($region->getPostalCodes())) {
            foreach ($region->getPostalCodes() as $postalCode) {
                $postalCode->setRegion();
            }
        }
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_region_index');
    }
    
    /**
     * @Route("/region/removeMany/{ids}", name="paprec_catalog_region_removeMany")
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
            
            /** @var Region[] $regions */
            $regions = $em->getRepository('PaprecCatalogBundle:Region')->findById($ids);
            
            foreach ($regions as $region) {
                $region->setDeleted(new DateTime);
                if ($region->getPostalCodes() && count($region->getPostalCodes())) {
                    foreach ($region->getPostalCodes() as $postalCode) {
                        $postalCode->setRegion();
                    }
                }
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_catalog_region_index');
    }
}
