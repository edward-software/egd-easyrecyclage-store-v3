<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\UserMyProfileType;
use App\Form\UserType;
use DateTime;
use Doctrine\ORM\QueryBuilder;
use Exception;
use PhpOffice\PhpSpreadsheet\Exception as PSException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/", name="paprec_user_user_index")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('user/user/index.html.twig');
    }
    
    /**
     * @Route("/loadList", name="paprec_user_user_loadList")
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

        $cols['id'] = ['label' => 'id', 'id' => 'u.id', 'method' => ['getId']];
        $cols['username'] = ['label' => 'username', 'id' => 'u.username', 'method' => ['getUsername']];
        $cols['firstName'] = ['label' => 'firstName', 'id' => 'u.first_name', 'method' => ['getFirstName']];
        $cols['lastName'] = ['label' => 'lastName', 'id' => 'u.last_nName', 'method' => ['getLastName']];
        $cols['email'] = ['label' => 'email', 'id' => 'u.email', 'method' => ['getEmail']];
        $cols['enabled'] = ['label' => 'enabled', 'id' => 'u.enabled', 'method' => ['isEnabled']];
        $cols['dateCreation'] = ['label' => 'dateCreation', 'id' => 'u.date_creation', 'method' => ['getDateCreation'], 'filter' => [
            ['name' => 'format', 'args' => ['Y-m-d H:i:s']]]
        ];

        $em = $this->getDoctrine()->getManager();
        
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $em->createQueryBuilder();

        $queryBuilder
            ->select(['u'])
            ->from('PaprecUserBundle:User', 'u')
            ->where('u.deleted IS NULL');

        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) === '#') {
                $queryBuilder
                    ->andWhere(
                        $queryBuilder
                            ->expr()
                            ->orx(
                                $queryBuilder
                                    ->expr()
                                    ->eq('u.id', '?1')
                            )
                    )
                    ->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder
                    ->andWhere(
                        $queryBuilder
                            ->expr()
                            ->orx(
                                $queryBuilder->expr()->like('u.username', '?1'),
                                $queryBuilder->expr()->like('u.first_name', '?1'),
                                $queryBuilder->expr()->like('u.last_name', '?1'),
                                $queryBuilder->expr()->like('u.email', '?1'),
                                $queryBuilder->expr()->like('u.date_creation', '?1')
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
     * @Route("/export", name="paprec_user_user_export")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request     $request
     * @param Translator  $translator
     * @param Spreadsheet $spreadsheet
     *
     * @return mixed
     * @throws PSException
     */
    public function exportAction(Request $request, Translator $translator, Spreadsheet $spreadsheet)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $em->createQueryBuilder();

        $queryBuilder
            ->select(['u'])
            ->from('PaprecUserBundle:User', 'u')
            ->where('u.deleted is NULL');

        /** @var User[] $users */
        $users = $queryBuilder->getQuery()->getResult();
    
        $spreadsheet
            ->getProperties()
            ->setCreator("Reisswolf Shop")
            ->setLastModifiedBy("Reisswolf Shop")
            ->setTitle("Reisswolf Shop - USers")
            ->setSubject("Extact");
    
        $spreadsheet
            ->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Company')
            ->setCellValue('C1', 'First name')
            ->setCellValue('D1', 'Last name')
            ->setCellValue('E1', 'Email')
            ->setCellValue('F1', 'Username')
            ->setCellValue('G1', 'Roles')
            ->setCellValue('H1', 'Postal codes')
            ->setCellValue('I1', 'Enabled')
            ->setCellValue('J1', 'Creation date');
    
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Users');
        
        $i = 2;

        if ($users && is_iterable($users) && count($users)) {
            foreach ($users as $user) {
                $roles = [];

                if ($user && is_iterable($user->getRoles()) && count($user->getRoles()))
                    foreach ($user->getRoles() as $role) {
                        if ($role !== 'ROLE_USER') {
                            $roles[] = $translator->trans($role);
                        }
                    }

                $postalCodesArr = [];
                if ($user && is_iterable($user->getPostalCodes()) && count($user->getPostalCodes())) {
                    
                    $postalCodes = $user->getPostalCodes();
                    foreach ($postalCodes as $postalCode) {
                        $postalCodesArr[] = $postalCode->getCode();
                    }
                }
    
                $spreadsheet
                    ->setActiveSheetIndex(0)
                    ->setCellValue('A' . $i, $user->getId())
                    ->setCellValue('B' . $i, $user->getCompanyName())
                    ->setCellValue('C' . $i, $user->getFirstName())
                    ->setCellValue('D' . $i, $user->getLastName())
                    ->setCellValue('E' . $i, $user->getEmail())
                    ->setCellValue('F' . $i, $user->getUsername())
                    ->setCellValue('G' . $i, implode(',', $roles))
                    ->setCellValue('H' . $i, implode(',', $postalCodesArr))
                    ->setCellValue('I' . $i, $translator->trans('General.' . $user->isEnabled()))
                    ->setCellValue('J' . $i, $user->getDateCreation()->format('Y-m-d'));
                $i++;
            }
        }
    
        $writer = new Xlsx($spreadsheet);
    
        $fileName = 'ReisswolfShop-Extract-Users-' . date('Y-m-d') . '.xlsx';
    
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
     * @Route("/view/{id}", name="paprec_user_user_view")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request $request
     * @param User    $user
     *
     * @return Response
     */
    public function viewAction(Request $request, User $user)
    {
        if ($user->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        return $this->render('user/user/view.html.twig', [
            'user' => $user
        ]);
    }
    
    /**
     * @Route("/add", name="paprec_user_user_add")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     */
    public function addAction(Request $request)
    {
        /** @var User $user */
        $user = new User();

        $roles = [];
        foreach ($this->getParameter('security.role_hierarchy.roles') as $role => $children) {
            $roles[$role] = $role;
        }
        
        $languages = [];
        foreach ($this->getParameter('paprec_languages') as $language) {
            $languages[$language] = $language;
        }

        $form = $this->createForm(UserType::class, $user, [
            'roles' => $roles,
            'languages' => $languages
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();
            $user->setDateCreation(new DateTime);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('paprec_user_user_view', [
                'id' => $user->getId()
            ]);
        }

        return $this->render('user/user/add.html.twig', [
            'form' => $form->createView()
        ]);
    }
    
    /**
     * @Route("/edit/{id}", name="paprec_user_user_edit")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request $request
     * @param User    $user
     *
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function editAction(Request $request, User $user)
    {
        if ($user->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        $roles = [];
        foreach ($this->getParameter('security.role_hierarchy.roles') as $role => $children) {
            $roles[$role] = $role;
        }

        $languages = [];
        foreach ($this->getParameter('paprec_languages') as $language) {
            $languages[$language] = $language;
        }

        $form = $this->createForm(UserType::class, $user, [
            'roles' => $roles,
            'languages' => $languages
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();
            $user->setDateUpdate(new DateTime);

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_user_user_view', [
                'id' => $user->getId()
            ]);
        }

        return $this->render('user/user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }
    
    /**
     * @Route("/editMyProfile", name="paprec_user_user_editMyProfile")
     * @Security("has_role('ROLE_USER')")
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function editMyProfileAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $languages = [];
        foreach ($this->getParameter('paprec_languages') as $language) {
            $languages[$language] = $language;
        }

        $form = $this->createForm(UserMyProfileType::class, $user, [
            'languages' => $languages
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();
            $user->setDateUpdate(new DateTime);

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_home_dashboard');

        }

        return $this->render('user/user/editMyProfile.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }
    
    /**
     * @Route("/sendAccess/{id}", name="paprec_user_user_sendAccess")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request                 $request
     * @param User                    $user
     * @param Swift_Mailer            $mailer
     * @param TokenGeneratorInterface $tokenGenerator
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function sendAccessAction(Request $request, User $user, Swift_Mailer $mailer, TokenGeneratorInterface $tokenGenerator)
    {
        if (!$user->isEnabled()) {
            $this
                ->get('session')
                ->getFlashBag()
                ->add('errors', 'userIsNotEnabled');

            return $this->redirectToRoute('paprec_user_user_view', [
                'id' => $user->getId()
            ]);
        }

        $password = substr($tokenGenerator->generateToken(), 0, 8);

        $user->setPassword($password);
        $user->setDateUpdate(new DateTime);
        
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        
        $message = (new Swift_Message('Easy-Recyclage : Identifiants'))
            ->setFrom($_ENV['MAILER_PAPREC_SENDER'])
            ->setTo($user->getEmail())
            ->setBody(
                $this->render(
                    'user/user/sendAccessEmail.html.twig', [
                        'user' => $user,
                        'password' => $password,
                    ]
                ),
                'text/html'
            )
        ;
        
        if ($mailer->send($message)) {
            $this
                ->get('session')
                ->getFlashBag()
                ->add('success', 'accessHasBeenSent');
        } else {
            $this
                ->get('session')
                ->getFlashBag()
                ->add('error', 'accessCannotBeSent');
        }
        
        return $this->redirectToRoute('paprec_user_user_view', [
            'id' => $user->getId()
        ]);
    }
    
    /**
     * @Route("/sendAccessMany/{ids}", name="paprec_user_user_sendAccessMany")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request                 $request
     * @param Swift_Mailer            $mailer
     * @param TokenGeneratorInterface $tokenGenerator
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function sendAccessManyAction(Request $request, Swift_Mailer $mailer, TokenGeneratorInterface $tokenGenerator)
    {
        $em = $this->getDoctrine()->getManager();
        
        $ids = $request->get('ids');

        if (!$ids) {
            throw new NotFoundHttpException();
        }

        $ids = explode(',', $ids);

        if (is_array($ids) && count($ids)) {
            
            /** @var User[] $users */
            $users = $em->getRepository('PaprecUserBundle:User')->findById($ids);
            
            foreach ($users as $user) {
                if ($user->isEnabled()) {
                    $password = substr($tokenGenerator->generateToken(), 0, 8);

                    $user->setPassword($password);
                    $user->setDateUpdate(new DateTime);
                    $em->flush();
    
                    $message = (new Swift_Message('Easy-Recyclage : Identifiants'))
                        ->setFrom($_ENV['MAILER_PAPREC_SENDER'])
                        ->setTo($user->getEmail())
                        ->setBody(
                            $this->render(
                                'user/user/sendAccessEmail.html.twig', [
                                    'user' => $user,
                                    'password' => $password,
                                ]
                            ),
                            'text/html'
                        )
                    ;
                    
                    if ($mailer->send($message)) {
                        $this
                            ->get('session')
                            ->getFlashBag()
                            ->add('success', [
                                'msg' => 'accessHasBeenSent',
                                'var' => $user->getEmail()
                            ]);
                    } else {
                        $this
                            ->get('session')
                            ->getFlashBag()
                            ->add('error', [
                                'msg' => 'accessCannotBeSent',
                                'var' => $user->getEmail()
                            ]);
                    }
                }
            }
        }

        return $this->redirectToRoute('paprec_user_user_index');
    }
    
    /**
     * @Route("/remove/{id}", name="paprec_user_user_remove")
     * @Security("has_role('ROLE_COMMERCIAL')")
     *
     * @param Request $request
     * @param User    $user
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function removeAction(Request $request, User $user)
    {
        $em = $this->getDoctrine()->getManager();

        /**
         * On modifie l'email et l'username qui sont uniques dans FOSUser
         * Ainsi on pourra de nouveau ajouté qqun avec le même username
         */
        $deletedUsername = substr($user->getUsernameCanonical() .  uniqid(), 0, 255);
        $deletedEmail = substr($user->getEmail() .  uniqid(), 0, 255);
        
        $user->setUsername($deletedUsername);
        $user->setUsernameCanonical($deletedUsername);
        $user->setEmail($deletedEmail);
        $user->setEmailCanonical($deletedEmail);

        $user->setDeleted(new DateTime);
        $user->setEnabled(false);
        $em->flush();

        return $this->redirectToRoute('paprec_user_user_index');
    }
    
    /**
     * @Route("/removeMany/{ids}", name="paprec_user_user_removeMany")
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
            
            /** @var User[] $users */
            $users = $em->getRepository('PaprecUserBundle:User')->findById($ids);
            
            foreach ($users as $user) {
                /**
                 * On modifie l'email et l'username qui sont uniques dans FOSUser
                 * Ainsi on pourra de nouveau ajouté qqun avec le même username
                 */
                $deletedUsername = substr($user->getUsernameCanonical() . uniqid(), 0, 255);
                $deletedEmail = substr($user->getEmail() .  uniqid(), 0, 255);
                $user->setUsername($deletedUsername);
                $user->setUsernameCanonical($deletedUsername);
                $user->setEmail($deletedEmail);
                $user->setEmailCanonical($deletedEmail);

                $user->setDeleted(new DateTime);
                $user->setEnabled(false);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_user_user_index');
    }
}
