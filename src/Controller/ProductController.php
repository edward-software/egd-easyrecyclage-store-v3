<?php
declare(strict_types=1);

namespace App\Controller;


use App\Entity\Picture;
use App\Entity\Product;
use App\Entity\ProductLabel;
use App\Entity\User;
use App\Form\PictureProductType;
use App\Form\ProductLabelType;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\NumberManager;
use App\Service\PictureManager;
use App\Service\ProductLabelManager;
use App\Service\ProductManager;
use App\Tools\DataTable;
use DateTime;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\Exception as PSException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductController extends AbstractController
{
    /**
     * @Route("/product", name="paprec_catalog_product_index")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('catalog/product/index.html.twig');
    }
    
    /**
     * @Route("/product/loadList", name="paprec_catalog_product_loadList")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request            $request
     * @param DataTable          $dataTable
     * @param PaginatorInterface $paginator
     *
     * @return JsonResponse
     */
    public function loadListAction(
        Request $request,
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

        $cols['id'] = ['label' => 'id', 'id' => 'p.id', 'method' => ['getId']];
        $cols['name'] = ['label' => 'name', 'id' => 'pL.name', 'method' => [['getProductLabels', 0], 'getName']];
        $cols['dimensions'] = ['label' => 'dimensions', 'id' => 'p.dimensions', 'method' => ['getDimensions']];
        $cols['isEnabled'] = ['label' => 'isEnabled', 'id' => 'p.is_enabled', 'method' => ['getIsEnabled']];

        $em = $this->getDoctrine()->getManager();
        
        /** @var ProductRepository $productRepository */
        $productRepository = $em->getRepository(Product::class);
        
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $productRepository
            ->createQueryBuilder('p')
            ->select(['p', 'pL'])
            ->leftJoin('p.productLabels', 'pL')
            ->where('p.deleted IS NULL')
            ->andWhere('pL.language = :language')
            ->setParameter('language', 'EN');

        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) === '#') {
                $queryBuilder
                    ->andWhere(
                        $queryBuilder
                            ->expr()
                            ->orx(
                                $queryBuilder
                                    ->expr()
                                    ->eq('p.id', '?1')
                            )
                    )
                    ->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder
                    ->andWhere(
                        $queryBuilder
                            ->expr()
                            ->orx(
                                $queryBuilder->expr()->like('pL.name', '?1'),
                                $queryBuilder->expr()->like('p.dimensions', '?1'),
                                $queryBuilder->expr()->like('pL.is_enabled', '?1')
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
            $line['isEnabled'] = $data['isEnabled'] ? $translator->trans('General.1') : $translator->trans('General.0');
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
     * @Route("/product/export",  name="paprec_catalog_product_export")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request        $request
     * @param ProductManager $productManager
     * @param NumberManager  $numberManager
     * @param Spreadsheet    $spreadsheet
     *
     * @return mixed
     * @throws PSException
     */
    public function exportAction(
        Request $request,
        ProductManager $productManager,
        NumberManager $numberManager,
        Spreadsheet $spreadsheet,
        TranslatorInterface $translator
    )
    {
        $language = $request->getLocale();

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(['p'])
            ->from(Product::class, 'p')
            ->where('p.deleted IS NULL');

        $products = $queryBuilder->getQuery()->getResult();
    
        $spreadsheet->getProperties()->setCreator("Reisswolf Shop")
            ->setLastModifiedBy("Reisswolf Shop")
            ->setTitle("Reisswolf Shop - Products")
            ->setSubject("Extract");

        $sheet = $spreadsheet->setActiveSheetIndex(0);
        $sheet->setTitle('Products');
        
        // Labels
        $sheetLabels = [
            'P. ID',
            'Creation date',
            'Update date',
            'Deleted',
            'Capacity',
            'Capacity unit',
            'Dimensions',
            'is Enabled',
            'Rental unit price',
            'Transport UP',
            'Treatment UP',
            'Traceability UP',
            'Position',
            'User creation ID',
            'User update ID',
            'Folder number',
            'Setup UP',
            'PL. ID',
            'Name',
            'Short desc.',
            'Language',
            'Product version',
            'Lock type',
        ];
        
        $xAxe = 'A';
        foreach ($sheetLabels as $label) {
            $sheet->setCellValue($xAxe . 1, $label);
            $xAxe++;
        }
        
        $yAxe = 2;
        
        /** @var Product[] $products */
        foreach ($products as $product) {

            /** @var ProductLabel $productLabel */
            $productLabel = $productManager->getProductLabelByProductAndLocale($product, strtoupper($language));
            
            // Getters
            $getters = [
                $product->getId(),
                $product->getDateCreation()->format('Y-m-d'),
                $product->getDateUpdate() ? $product->getDateUpdate()->format('Y-m-d') : '',
                $product->getDeleted() ? 'true' : 'false',
                $product->getCapacity(),
                $product->getCapacityUnit(),
                $product->getDimensions(),
                $product->getIsEnabled(),
                $numberManager->denormalize($product->getRentalUnitPrice()),
                $numberManager->denormalize($product->getTransportUnitPrice()),
                $numberManager->denormalize($product->getTreatmentUnitPrice()),
                $numberManager->denormalize($product->getTraceabilityUnitPrice()),
                $product->getPosition(),
                $product->getUserCreation(),
                $product->getUserUpdate(),
                $product->getFolderNumber(),
                $numberManager->denormalize($product->getSetUpPrice()),
                $productLabel->getId(),
                $productLabel->getName(),
                $productLabel->getShortDescription(),
                $productLabel->getLanguage(),
                $productLabel->getVersion(),
                $productLabel->getLockType(),
            ];
            
            $xAxe = 'A';
            foreach ($getters as $getter) {
                $sheet->setCellValue($xAxe . $yAxe, (string) $getter);
                $xAxe++;
            }
            $yAxe++;
        }
    
        // Format
        $sheet->getStyle("A1:" . $sheet->getHighestDataColumn() . 1)->getAlignment()->setHorizontal('center');
        $sheet->getStyle("A2:" . $sheet->getHighestDataColumn() . $sheet->getHighestDataRow())->getAlignment()->setHorizontal('left');
    
        // Resize columns
        for ($i = 'A'; $i <= $sheet->getHighestDataColumn(); $i++) {
            $sheet->getColumnDimension($i)->setAutoSize(true);
        }
    
        $writer = new Xlsx($spreadsheet);

        $fileName = 'ReisswolfShop-Extraction-Products-' . date('Y-m-d') . '.xlsx';
    
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
     * @Route("/product/view/{id}",  name="paprec_catalog_product_view")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request        $request
     * @param Product        $product
     * @param ProductManager $productManager
     *
     * @return Response
     * @throws EntityNotFoundException
     */
    public function viewAction(Request $request, Product $product, ProductManager $productManager)
    {
        $productManager->isDeleted($product, true);

        $language = $request->getLocale();
        
        /** @var ProductLabel $productLabel */
        $productLabel = $productManager->getProductLabelByProductAndLocale($product, strtoupper($language));

        /** @var ProductLabel[] $otherProductLabels */
        $otherProductLabels = $productManager->getProductLabels($product);

        $tmp = [];
        foreach ($otherProductLabels as $pL) {
            if ($pL->getId() != $productLabel->getId()) {
                $tmp[] = $pL;
            }
        }
        $otherProductLabels = $tmp;
        
        foreach ($this->getParameter('paprec_types_picture') as $type) {
            $types[$type] = $type;
        }

        $picture = new Picture();

        $formAddPicture = $this->createForm(PictureProductType::class, $picture, [
            'types' => $types
        ]);

        $formEditPicture = $this->createForm(PictureProductType::class, $picture, [
            'types' => $types
        ]);
        
        return $this->render('catalog/product/view.html.twig', [
            'product' => $product,
            'productLabel' => $productLabel,
            'formAddPicture' => $formAddPicture->createView(),
            'formEditPicture' => $formEditPicture->createView(),
            'otherProductLabels' => $otherProductLabels
        ]);
    }
    
    /**
     * @Route("/product/add",  name="paprec_catalog_product_add")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request       $request
     * @param NumberManager $numberManager
     *
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function addAction(Request $request, NumberManager $numberManager)
    {
        /** @var User $user */
        $user = $this->getUser();

        $languages = [];
        foreach ($this->getParameter('paprec_languages') as $language) {
            $languages[$language] = $language;
        }

        /** @var Product $product */
        $product = new Product();
        
        /** @var ProductLabel $productLabel */
        $productLabel = new ProductLabel();

        $form1 = $this->createForm(ProductType::class, $product);
        $form2 = $this->createForm(ProductLabelType::class, $productLabel, [
            'languages' => $languages,
            'language' => 'EN'
        ]);

        $form1->handleRequest($request);
        $form2->handleRequest($request);
 
        if ($form1->isSubmitted() && $form1->isValid() && $form2->isSubmitted() && $form2->isValid()) {
    
            //dd($form1);
            
            $em = $this->getDoctrine()->getManager();
            $product = $form1->getData();
            
            $product->setRentalUnitPrice($numberManager->normalize($product->getRentalUnitPrice()));
            $product->setSetUpPrice($numberManager->normalize($product->getSetUpPrice()));
            $product->setTransportUnitPrice($numberManager->normalize($product->getTransportUnitPrice()));
            $product->setTreatmentUnitPrice($numberManager->normalize($product->getTreatmentUnitPrice()));
            $product->setTraceabilityUnitPrice($numberManager->normalize($product->getTraceabilityUnitPrice()));

            $product->setDateCreation(new DateTime);
            $product->setUserCreation($user);

            $em->persist($product);
            $em->flush();

            $productLabel = $form2->getData();
            $productLabel->setDateCreation(new DateTime);
            $productLabel->setUserCreation($user);
            $productLabel->setProduct($product);

            $em->persist($productLabel);
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_product_view', [
                'id' => $product->getId()
            ]);
        }

        return $this->render('catalog/product/add.html.twig', [
            'form1' => $form1->createView(),
            'form2' => $form2->createView()
        ]);
    }
    
    /**
     * @Route("/product/edit/{id}",  name="paprec_catalog_product_edit")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request        $request
     * @param Product        $product
     * @param ProductManager $productManager
     * @param NumberManager  $numberManager
     *
     * @return RedirectResponse|Response
     * @throws EntityNotFoundException
     */
    public function editAction(Request $request, Product $product, ProductManager $productManager, NumberManager $numberManager)
    {
        $productManager->isDeleted($product, true);

        /** @var User $user */
        $user = $this->getUser();

        $languages = [];
        foreach ($this->getParameter('paprec_languages') as $language) {
            $languages[$language] = $language;
        }

        $language = $request->getLocale();
        
        /** @var ProductLabel $productLabel */
        $productLabel = $productManager->getProductLabelByProductAndLocale($product, strtoupper($language));

        $product->setSetUpPrice($numberManager->denormalize($product->getSetUpPrice()));
        $product->setRentalUnitPrice($numberManager->denormalize($product->getRentalUnitPrice()));
        $product->setTransportUnitPrice($numberManager->denormalize($product->getTransportUnitPrice()));
        $product->setTreatmentUnitPrice($numberManager->denormalize($product->getTreatmentUnitPrice()));
        $product->setTraceabilityUnitPrice($numberManager->denormalize($product->getTraceabilityUnitPrice()));

        $form1 = $this->createForm(ProductType::class, $product);
        $form2 = $this->createForm(ProductLabelType::class, $productLabel, [
            'languages' => $languages,
            'language' => $productLabel->getLanguage()
        ]);

        $form1->handleRequest($request);
        $form2->handleRequest($request);

        if ($form1->isSubmitted() && $form1->isValid() && $form2->isSubmitted() && $form2->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $product = $form1->getData();

            $product->setSetUpPrice($numberManager->normalize($product->getSetUpPrice()));
            $product->setRentalUnitPrice($numberManager->normalize($product->getRentalUnitPrice()));
            $product->setTransportUnitPrice($numberManager->normalize($product->getTransportUnitPrice()));
            $product->setTreatmentUnitPrice($numberManager->normalize($product->getTreatmentUnitPrice()));
            $product->setTraceabilityUnitPrice($numberManager->normalize($product->getTraceabilityUnitPrice()));
            $product->setDateUpdate(new DateTime);
            $product->setUserUpdate($user);
            
            $em->flush();

            $productLabel = $form2->getData();
            $productLabel->setDateUpdate(new DateTime);
            $productLabel->setUserUpdate($user);
            $productLabel->setProduct($product);

            $em->flush();

            return $this->redirectToRoute('paprec_catalog_product_view', [
                'id' => $product->getId()
            ]);
        }
        return $this->render('catalog/product/edit.html.twig', [
            'form1' => $form1->createView(),
            'form2' => $form2->createView(),
            'product' => $product,
            'productLabel' => $productLabel
        ]);
    }
    
    /**
     * @Route("/product/remove/{id}", name="paprec_catalog_product_remove")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request $request
     * @param Product $product
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function removeAction(Request $request, Product $product)
    {
        $em = $this->getDoctrine()->getManager();

        //Suppression des images
        
        /** @var Picture[] $pictures */
        $pictures = $product->getPictures();
        
        foreach ($pictures as $picture) {
            $this->removeFile($this->getParameter('paprec_catalog.product.di.picto_path') . '/' . $picture->getPath());
            $product->removePicture($picture);
        }

        $product->setDeleted(new DateTime);
        $product->setIsEnabled(false);
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_product_index');
    }
    
    /**
     * @Route("/product/removeMany/{ids}", name="paprec_catalog_product_removeMany")
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
            
            /** @var Product[] $products */
            $products = $em->getRepository(Product::class)->findById($ids);
            
            foreach ($products as $product) {
                foreach ($product->getPictures() as $picture) {
                    $this->removeFile($this->getParameter('paprec_catalog.product.picto_path') . '/' . $picture->getPath());
                    $product->removePicture($picture);
                }

                $product->setDeleted(new DateTime());
                $product->setIsEnabled(false);
            }
            
            $em->flush();
        }

        return $this->redirectToRoute('paprec_catalog_product_index');
    }
    
    /**
     * @Route("/product/enableMany/{ids}", name="paprec_catalog_product_enableMany")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function enableManyAction(Request $request)
    {
        $ids = $request->get('ids');

        if (!$ids) {
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        $ids = explode(',', $ids);

        if (is_array($ids) && count($ids)) {
            
            /** @var Product[] $products */
            $products = $em->getRepository('PaprecCatalogBundle:Product')->findById($ids);
            
            foreach ($products as $product) {
                $product->setIsEnabled(true);
            }
            
            $em->flush();
        }
        
        return $this->redirectToRoute('paprec_catalog_product_index');
    }
    
    /**
     * @Route("/product/disableMany/{ids}", name="paprec_catalog_product_disableMany")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function disableManyAction(Request $request)
    {
        $ids = $request->get('ids');

        if (!$ids) {
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        $ids = explode(',', $ids);

        if (is_array($ids) && count($ids)) {
            
            /** @var Product[] $products */
            $products = $em->getRepository(Product::class)->findById($ids);
            
            foreach ($products as $product) {
                $product->setIsEnabled(false);
            }
            
            $em->flush();
        }
        
        return $this->redirectToRoute('paprec_catalog_product_index');
    }
    
    /**
     * @Route("/product/{id}/addProductLabel",  name="paprec_catalog_product_addProductLabel")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request        $request
     * @param Product        $product
     * @param ProductManager $productManager
     *
     * @return RedirectResponse|Response
     * @throws EntityNotFoundException
     */
    public function addProductLabelAction(Request $request, Product $product, ProductManager $productManager)
    {
        /** @var User $user */
        $user = $this->getUser();

        $productManager->isDeleted($product, true);

        $languages = [];
        foreach ($this->getParameter('paprec_languages') as $language) {
            $languages[$language] = $language;
        }
        $productLabel = new ProductLabel();

        $form = $this->createForm(ProductLabelType::class, $productLabel, [
            'languages' => $languages,
            'language' => strtoupper($request->getLocale())
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $productLabel = $form->getData();
            $productLabel->setDateCreation(new DateTime);
            $productLabel->setUserCreation($user);
            $productLabel->setProduct($product);

            $em->persist($productLabel);
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_product_view', [
                'id' => $product->getId()
            ]);
        }

        return $this->render('catalog/product/productLabel/add.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }
    
    /**
     * @Route("/product/{id}/editProductLabel/{productLabelId}",  name="paprec_catalog_product_editProductLabel")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request             $request
     * @param Product             $product
     * @param                     $productLabelId
     * @param ProductManager      $productManager
     * @param ProductLabelManager $productLabelManager
     *
     * @return RedirectResponse|Response
     * @throws EntityNotFoundException
     */
    public function editProductLabelAction(
        Request $request,
        Product $product,
        $productLabelId,
        ProductManager $productManager,
        ProductLabelManager $productLabelManager
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $productManager->isDeleted($product, true);

        /** @var ProductLabel $productLabel */
        $productLabel = $productLabelManager->get($productLabelId);

        $languages = [];
        foreach ($this->getParameter('paprec_languages') as $language) {
            $languages[$language] = $language;
        }

        $form = $this->createForm(ProductLabelType::class, $productLabel, [
            'languages' => $languages,
            'language' => $productLabel->getLanguage()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $productLabel = $form->getData();
            $productLabel->setDateUpdate(new DateTime);
            $productLabel->setUserUpdate($user);

//            $em->merge($productLabel);
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_product_view', [
                'id' => $product->getId()
            ]);
        }

        return $this->render('catalog/product/productLabel/edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product
        ]);
    }
    
    /**
     * @Route("/product/{id}/removeProductLabel/{productLabelId}",  name="paprec_catalog_product_removeProductLabel")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request             $request
     * @param Product             $product
     * @param                     $productLabelId
     * @param ProductManager      $productManager
     * @param ProductLabelManager $productLabelManager
     *
     * @return RedirectResponse
     * @throws EntityNotFoundException
     */
    public function removeProductLabelAction(
        Request $request,
        Product $product,
        $productLabelId,
        ProductManager $productManager,
        ProductLabelManager $productLabelManager
    )
    {
        $em = $this->getDoctrine()->getManager();

        $productManager->isDeleted($product, true);

        /** @var ProductLabel $productLabel */
        $productLabel = $productLabelManager->get($productLabelId);
        
        $em->remove($productLabel);
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_product_view', [
            'id' => $product->getId()
        ]);
    }
    
    /**
     * Supprimme un fichier du sytème de fichiers
     *
     * @param $path
     *
     * @throws Exception
     */
    public function removeFile($path)
    {
        $fs = new Filesystem();
        try {
            $fs->remove($path);
        } catch (IOException $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    /**
     * @Route("/product/addPicture/{id}/{type}", name="paprec_catalog_product_addPicture", methods={"POST"})
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request $request
     * @param Product $product
     *
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function addPictureAction(Request $request, Product $product)
    {
        $picture = new Picture();
        foreach ($this->getParameter('paprec_types_picture') as $type) {
            $types[$type] = $type;
        }

        $form = $this->createForm(PictureProductType::class, $picture, [
            'types' => $types
        ]);

        $em = $this->getDoctrine()->getManager();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $product->setDateUpdate(new DateTime());
            $picture = $form->getData();

            if ($picture->getPath() instanceof UploadedFile) {
                $pic = $picture->getPath();
                $pictoFileName = md5(uniqid()) . '.' . $pic->guessExtension();

                $pic->move($this->getParameter('paprec_catalog.product.picto_path'), $pictoFileName);

                $picture->setPath($pictoFileName);
                $picture->setType($request->get('type'));
                $picture->setProduct($product);
                $product->addPicture($picture);
                $em->persist($picture);
                $em->flush();
            }

            return $this->redirectToRoute('paprec_catalog_product_view', [
                'id' => $product->getId()
            ]);
        }
        return $this->render('catalog/product/view.html.twig', [
            'product' => $product,
            'formAddPicture' => $form->createView()
        ]);
    }
    
    /**
     * @Route("/product/editPicture/{id}/{pictureID}", name="paprec_catalog_product_editPicture", methods={"POST"})
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request        $request
     * @param Product        $product
     * @param ProductManager $productManager
     * @param PictureManager $pictureManager
     *
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function editPictureAction(
        Request $request,
        Product $product,
        ProductManager $productManager,
        PictureManager $pictureManager
    )
    {
        $em = $this->getDoctrine()->getManager();
        
        $pictureID = $request->get('pictureID');
        $picture = $pictureManager->get($pictureID);
        $oldPath = $picture->getPath();

        foreach ($this->getParameter('paprec_types_picture') as $type) {
            $types[$type] = $type;
        }

        $form = $this->createForm(PictureProductType::class, $picture, [
            'types' => $types
        ]);


        $form->handleRequest($request);
        if ($form->isValid()) {
            $product->setDateUpdate(new DateTime());
            $picture = $form->getData();

            if ($picture->getPath() instanceof UploadedFile) {
                $pic = $picture->getPath();
                $pictoFileName = md5(uniqid()) . '.' . $pic->guessExtension();

                $pic->move($this->getParameter('paprec_catalog.product.picto_path'), $pictoFileName);

                $picture->setPath($pictoFileName);
                $this->removeFile($this->getParameter('paprec_catalog.product.picto_path') . '/' . $oldPath);
                
                $em->flush();
            }

            return $this->redirectToRoute('paprec_catalog_product_view', [
                'id' => $product->getId()
            ]);
        }
        return $this->render('catalog/product/view.html.twig', [
            'product' => $product,
            'formEditPicture' => $form->createView()
        ]);
    }
    
    /**
     * @Route("/product/removePicture/{id}/{pictureID}", name="paprec_catalog_product_removePicture")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request $request
     * @param Product $product
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function removePictureAction(Request $request, Product $product)
    {
        $em = $this->getDoctrine()->getManager();

        $pictureID = $request->get('pictureID');
        $pictures = $product->getPictures();
        
        foreach ($pictures as $picture) {
            if ($picture->getId() == $pictureID) {
                $product->setDateUpdate(new DateTime());
                $this->removeFile($this->getParameter('paprec_catalog.product.picto_path') . '/' . $picture->getPath());
                $em->remove($picture);
                continue;
            }
        }
        
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_product_view', [
            'id' => $product->getId()
        ]);
    }
    
    /**
     * @Route("/product/setPilotPicture/{id}/{pictureID}", name="paprec_catalog_product_setPilotPicture")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request $request
     * @param Product $product
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function setPilotPictureAction(Request $request, Product $product)
    {

        $em = $this->getDoctrine()->getManager();

        $pictureID = $request->get('pictureID');
        $pictures = $product->getPictures();
        
        foreach ($pictures as $picture) {
            if ($picture->getId() == $pictureID) {
                $product->setDateUpdate(new DateTime());
                $picture->setType('PILOTPICTURE');
                continue;
            }
        }
        
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_product_view', [
            'id' => $product->getId()
        ]);
    }
    
    /**
     * @Route("/product/setPicture/{id}/{pictureID}", name="paprec_catalog_product_setPicture")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request $request
     * @param Product $product
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function setPictureAction(Request $request, Product $product)
    {
        $em = $this->getDoctrine()->getManager();

        $pictureID = $request->get('pictureID');
        $pictures = $product->getPictures();
        
        foreach ($pictures as $picture) {
            if ($picture->getId() == $pictureID) {
                $product->setDateUpdate(new DateTime());
                $picture->setType('PICTURE');
                continue;
            }
        }
        
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_product_view', [
            'id' => $product->getId()
        ]);
    }
}
