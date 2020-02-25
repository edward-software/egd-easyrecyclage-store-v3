<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\CustomArea;
use App\Entity\Picture;
use App\Form\CustomAreaType;
use App\Form\PictureProductType;
use App\Service\CustomAreaManager;
use App\Service\PictureManager;
use DateTime;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CustomAreaController
 *
 * @package App\Controller
 */
class CustomAreaController extends AbstractController
{
    /**
     * @Route("/custom/area", name="custom_area")
     *
     * @return Response
     */
    public function index()
    {
        return $this->render('custom_area/index.html.twig', [
            'controller_name' => 'CustomAreaController',
        ]);
    }
    
    /**
     * @Route("/customarea", name="paprec_catalog_custom_area_index")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('custom_area/index.html.twig');
    }
    
    /**
     * @Route("/customarea/loadList", name="paprec_catalog_custom_area_loadList")
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
        $cols['code'] = ['label' => 'code', 'id' => 'r.code', 'method' => ['getCode']];
        $cols['isDisplayed'] = ['label' => 'isDisplayed', 'id' => 'r.isDisplayed', 'method' => ['getIsDisplayed']];
        $cols['language'] = ['label' => 'language', 'id' => 'r.language', 'method' => ['getLanguage']];
        
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->getDoctrine()->getManager()->getRepository(CustomArea::class)->createQueryBuilder('r');
        
        $queryBuilder
            ->select(['r'])
            ->where('r.deleted IS NULL');
        
        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) === '#') {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->eq('r.id', '?1')
                ))->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('r.code', '?1'),
                    $queryBuilder->expr()->like('r.isDisplayed', '?1'),
                    $queryBuilder->expr()->like('r.language', '?1')
                ))->setParameter(1, '%' . $search['value'] . '%');
            }
        }
        
        $datatable = $this->get('goondi_tools.datatable')->generateTable($cols, $queryBuilder, $pageSize, $start, $orders, $columns, $filters);
        
        // Reformatage de certaines données
        $tmp = [];
        foreach ($datatable['data'] as $data) {
            $line = $data;
            $line['isDisplayed'] = $data['isDisplayed'] ? $this->get('translator')->trans('General.1') : $this->get('translator')->trans('General.0');
            $tmp[] = $line;
        }
        
        $datatable['data'] = $tmp;
        
        $return['recordsTotal'] = $datatable['recordsTotal'];
        $return['recordsFiltered'] = $datatable['recordsTotal'];
        $return['data'] = $datatable['data'];
        $return['resultCode'] = 1;
        $return['resultDescription'] = "success";
        
        return new JsonResponse($return);
    }
    
    /**
     * @Route("/customarea/view/{id}", name="paprec_catalog_custom_area_view")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request $request
     * @param CustomArea $customArea
     * @param CustomAreaManager $customAreaManager
     *
     * @return Response
     * @throws EntityNotFoundException
     */
    public function viewAction(Request $request, CustomArea $customArea, CustomAreaManager $customAreaManager)
    {
        $customAreaManager->isDeleted($customArea, true);
        
        foreach ($this->getParameter('paprec_custom_area_types_picture') as $type) {
            $types[$type] = $type;
        }
        
        $picture = new Picture();
        
        $formAddPicture = $this->createForm(PictureProductType::class, $picture, [
            'types' => $types
        ]);
        
        $formEditPicture = $this->createForm(PictureProductType::class, $picture, [
            'types' => $types
        ]);
        
        return $this->render('catalog/customArea/view.html.twig', [
            'customArea' => $customArea,
            'formAddPicture' => $formAddPicture->createView(),
            'formEditPicture' => $formEditPicture->createView()
        ]);
    }
    
    /**
     * @Route("/customarea/add", name="paprec_catalog_custom_area_add")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function addAction(Request $request)
    {
        $user = $this->getUser();
        
        $customArea = new CustomArea();
        
        $codes = [];
        foreach ($this->getParameter('paprec_custom_area_codes') as $code) {
            $codes[$code] = $code;
        }
        
        $languages = [];
        foreach ($this->getParameter('paprec_languages') as $language) {
            $languages[$language] = $language;
        }
        
        $form = $this->createForm(CustomAreaType::class, $customArea, [
            'languages' => $languages,
            'language' => strtoupper($request->getLocale()),
            'codes' => $codes
        ]);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $customArea = $form->getData();
            
            $customArea->setDateCreation(new DateTime);
            $customArea->setUserCreation($user);
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($customArea);
            $em->flush();
            
            return $this->redirectToRoute('paprec_catalog_custom_area_view', [
                'id' => $customArea->getId()
            ]);
        }
        
        return $this->render('catalog/customArea/add.html.twig', [
            'form' => $form->createView()
        ]);
    }
    
    /**
     * @Route("/customarea/edit/{id}", name="paprec_catalog_custom_area_edit")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request $request
     * @param CustomArea $customArea
     * @param CustomAreaManager $customAreaManager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws EntityNotFoundException
     */
    public function editAction(Request $request, CustomArea $customArea, CustomAreaManager $customAreaManager)
    {
        $user = $this->getUser();
        
        $customAreaManager->isDeleted($customArea, true);
        
        $codes = [];
        foreach ($this->getParameter('paprec_custom_area_codes') as $code) {
            $codes[$code] = $code;
        }
        
        $languages = [];
        foreach ($this->getParameter('paprec_languages') as $language) {
            $languages[$language] = $language;
        }
        
        $form = $this->createForm(CustomAreaType::class, $customArea, [
            'languages' => $languages,
            'codes' => $codes,
            'language' => strtoupper($request->getLocale())
        ]);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $customArea = $form->getData();
            
            $customArea->setDateUpdate(new DateTime);
            $customArea->setUserUpdate($user);
            
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            
            return $this->redirectToRoute('paprec_catalog_custom_area_view', [
                'id' => $customArea->getId()
            ]);
        }
        
        return $this->render('catalog/customArea/edit.html.twig', [
            'form' => $form->createView(),
            'customArea' => $customArea
        ]);
    }
    
    /**
     * @Route("/customarea/remove/{id}", name="paprec_catalog_custom_area_remove")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request $request
     * @param CustomArea $customArea
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function removeAction(Request $request, CustomArea $customArea)
    {
        $em = $this->getDoctrine()->getManager();
        
        $customArea->setDeleted(new DateTime());
        /*
        * Suppression des images
         */
        foreach ($customArea->getPictures() as $picture) {
            $this->removeFile($this->getParameter('paprec_catalog.product.di.picto_path') . '/' . $picture->getPath());
            $customArea->removePicture($picture);
        }
        $em->flush();
        
        return $this->redirectToRoute('paprec_catalog_custom_area_index');
    }
    
    /**
     * @Route("/customarea/removeMany/{ids}", name="paprec_catalog_custom_area_removeMany")
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
            
            /** @var CustomArea[] $customAreas */
            $customAreas = $em->getRepository(CustomArea::class)->findById($ids);
            
            foreach ($customAreas as $customArea) {
                foreach ($customArea->getPictures() as $picture) {
                    $this->removeFile($this->getParameter('paprec_catalog.product.picto_path') . '/' . $picture->getPath());
                    $customArea->removePicture($picture);
                }
                
                $customArea->setDeleted(new DateTime());
                $customArea->setIsDisplayed(false);
            }
            $em->flush();
        }
        
        return $this->redirectToRoute('paprec_catalog_custom_area_index');
    }
    
    /**
     * @Route("/customarea/addPicture/{id}/{type}", name="paprec_catalog_custom_area_addPicture")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request $request
     * @param CustomArea $customArea
     *
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function addPictureAction(Request $request, CustomArea $customArea)
    {
        $picture = new Picture();
        
        foreach ($this->getParameter('paprec_custom_area_types_picture') as $type) {
            $types[$type] = $type;
        }
        
        $form = $this->createForm(PictureProductType::class, $picture, [
            'types' => $types
        ]);
        
        $em = $this->getDoctrine()->getManager();
        
        $form->handleRequest($request);
        if ($form->isValid()) {
            $customArea->setDateUpdate(new DateTime());
            $picture = $form->getData();
            
            if ($picture->getPath() instanceof UploadedFile) {
                $pic = $picture->getPath();
                $pictoFileName = md5(uniqid()) . '.' . $pic->guessExtension();
                
                $pic->move($this->getParameter('paprec_catalog.product.picto_path'), $pictoFileName);
                
                $picture->setPath($pictoFileName);
                $picture->setType($request->get('type'));
                $picture->setCustomArea($customArea);
                $customArea->addPicture($picture);
                $em->persist($picture);
                $em->flush();
            }
            
            return $this->redirectToRoute('paprec_catalog_custom_area_view', [
                'id' => $customArea->getId()
            ]);
        }
        
        return $this->render('catalog/customArea/view.html.twig', [
            'customArea' => $customArea,
            'formAddPicture' => $form->createView()
        ]);
    }
    
    /**
     * @Route("/customarea/editPicture/{id}/{pictureID}", name="paprec_catalog_custom_area_editPicture")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request $request
     * @param CustomArea $customArea
     *
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function editPictureAction(Request $request, CustomArea $customArea, PictureManager $pictureManager)
    {
        $em = $this->getDoctrine()->getManager();
        $pictureID = $request->get('pictureID');
        
        /** @var Picture $picture */
        $picture = $pictureManager->get($pictureID);
        
        $oldPath = $picture->getPath();
    
        foreach ($this->getParameter('paprec_custom_area_types_picture') as $type) {
            $types[$type] = $type;
        }
        
        $form = $this->createForm(PictureProductType::class, $picture, [
            'types' => $types
        ]);
        
        
        $form->handleRequest($request);
        if ($form->isValid()) {
            $customArea->setDateUpdate(new DateTime());
            $picture = $form->getData();
            
            if ($picture->getPath() instanceof UploadedFile) {
                $pic = $picture->getPath();
                $pictoFileName = md5(uniqid()) . '.' . $pic->guessExtension();
                
                $pic->move($this->getParameter('paprec_catalog.product.picto_path'), $pictoFileName);
                
                $picture->setPath($pictoFileName);
                $this->removeFile($this->getParameter('paprec_catalog.product.picto_path') . '/' . $oldPath);
                $em->flush();
            }
            
            return $this->redirectToRoute('paprec_catalog_custom_area_view', [
                'id' => $customArea->getId()
            ]);
        }
        return $this->render('catalog/customArea/view.html.twig', [
            'customArea' => $customArea,
            'formEditPicture' => $form->createView()
        ]);
    }
    
    /**
     * @Route("/customarea/removePicture/{id}/{pictureID}", name="paprec_catalog_custom_area_removePicture")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request $request
     * @param CustomArea $customArea
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function removePictureAction(Request $request, CustomArea $customArea)
    {
        $em = $this->getDoctrine()->getManager();
        $pictureID = $request->get('pictureID');
        
        /** @var Picture[] $pictures */
        $pictures = $customArea->getPictures();
        
        foreach ($pictures as $picture) {
            if ($picture->getId() == $pictureID) {
                $customArea->setDateUpdate(new DateTime());
                $this->removeFile($this->getParameter('paprec_catalog.product.picto_path') . '/' . $picture->getPath());
                $em->remove($picture);
                continue;
            }
        }
        $em->flush();
        
        return $this->redirectToRoute('paprec_catalog_custom_area_view', [
            'id' => $customArea->getId()
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
}
