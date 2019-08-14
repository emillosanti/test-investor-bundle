<?php

namespace SAM\InvestorBundle\Controller;

use SAM\AddressBookBundle\Exchange\Exchange;
use SAM\AddressBookBundle\Manager\ContactUpdateManager;
use SAM\CommonBundle\Controller\Controller;
use SAM\CommonBundle\Form\Model\DateRange;
use SAM\CommonBundle\Form\Type\SearchType;
use SAM\CommonBundle\Manager\LegalEntityManager;
use SAM\InvestorBundle\Entity\Board;
use SAM\InvestorBundle\Entity\InvestorLegalEntity;
use SAM\InvestorBundle\Repository\InvestorRepositoryInterface;
use SAM\InvestorBundle\Repository\InvestorLegalEntityRepositoryInterface;
use SAM\InvestorBundle\Form\Type\NextStepType;
use SAM\InvestorBundle\Form\Type\InvestorCloseType;
use SAM\InvestorBundle\Form\Type\InvestorSearchType;
use SAM\InvestorBundle\Entity\Investor;
use SAM\InvestorBundle\Form\Type\InvestorType;
use SAM\InvestorBundle\Manager\InvestorManager;
use SAM\InvestorBundle\Form\Type\InvestorImportType;
use SAM\SearchBundle\Manager\SearchEngineManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Psr\Log\LoggerInterface;

/**
 * Class InvestorController
 *
 * @Route("/lps")
 * @IsGranted("ROLE_INVESTOR_USER")
 */
class InvestorController extends Controller
{
    /**
     * @param Request $request
     * @param ObjectManager $om
     * @param SearchEngineManager $searchEngineManager
     * @return Response
     *
     * @throws \Exception
     * @Route("/", name="investor_list")
     * @Cache(vary={"X-Requested-With"})
     */
    public function listAction(Request $request, ObjectManager $om, SearchEngineManager $searchEngineManager, InvestorManager $investorManager, LegalEntityManager $legalEntityManager)
    {
        $criterias = $this->getLPSCriterias($legalEntityManager);

        $form = $this->createForm(InvestorSearchType::class, $criterias, [
            'min_investment' => (float)$this->getParameter('sam_investor.min_investment'),
            'max_investment' => (float)$this->getParameter('sam_investor.max_investment'),
        ]);
        $form->handleRequest($request);
        $nbItemsPerPage = $this->getParameter('items_per_page');

        if ($form->isSubmitted() && $form->isValid()) {
            $criterias = $form->getData();
        }

        $criterias['legalEntity'] = $legalEntityManager->getCurrentInvestmentLegalEntity();

        if ($request->query->has('export') && $request->query->get('export') == '1') {
            $writer = $investorManager->export($criterias);
            $response = new StreamedResponse(
                function () use ($writer) {
                    $writer->save('php://output');
                }
            );
            $dispositionHeader = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                'lps.xlsx'
            );
            $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'maxage=1');
            $response->headers->set('Content-Disposition', $dispositionHeader);

            return $response;
        }

        $investorLegalEntities = $searchEngineManager->getRepository(InvestorLegalEntityRepositoryInterface::class)
            ->findInvestorLegalEntities(
                $criterias,
                $this->getUser(),
                $request->query->getInt('page', 1)
            );

        $myInvestorsCount = $searchEngineManager->getRepository(InvestorLegalEntityRepositoryInterface::class)
            ->countMy($this->getUser(), $criterias);
        $allInvestorsCount = $searchEngineManager->getRepository(InvestorLegalEntityRepositoryInterface::class)
            ->countAll($this->getUser(), $criterias);

        $view = '@SAMInvestor/Investor/_search_results.html.twig';
        $params = [
                'investorLegalEntities' => $investorLegalEntities,
                'myInvestorsCount' => $myInvestorsCount,
                'allInvestorsCount' => $allInvestorsCount,
                'form' => $form->createView()
        ];

        if (!$request->isXmlHttpRequest()) {
            $view = '@SAMInvestor/Investor/list.html.twig';
        } else {
            return new JsonResponse([
                'exportButton' => 'table' === $criterias['layout'],
                'html' => $this->renderView($view, $params),
                'count' => $investorLegalEntities->getTotalItemCount()
            ]);
        }

        return $this->render($view, $params);
    }

    private function getLPSCriterias($legalEntityManager) {
        $end = new \DateTime();
        $start = (new \DateTime())->modify('-1 year');
        $start->setTime(0, 0, 0);
        $end->setTime(23, 59, 59);

        return [
            'layout' => 'list',
            'myInvestor' => false,
            'dateRange' => new DateRange($start, $end),
        ];
    }

    /**
     * @param Request $request
     * @param ObjectManager $om
     * @param Investor $investor
     *
     * @return Response
     *
     * @Route("/{investor}", name="investor_show", requirements={"investor" = "\d+"})
     */
    public function showAction(Request $request, ObjectManager $om, $investor)
    {
        $investor = $this->findEntity('investor', $investor);

        $form = $this->createForm(InvestorType::class, $investor, ['method' => 'POST']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $om->persist($investor);
            $om->flush();
            $this->addFlash('success', 'flash.investor.registered');

            return $this->redirectToRoute('investor_show', ['investor' => $investor->getId()]);
        }

        $steps = $om->getRepository('investor_step')->findBy([], ['position' => 'asc']);
        $documentCategories = $om->getRepository('investor_document_category')->findBy([], ['position' => 'asc']);

        return $this->render('@SAMInvestor/Investor/show.html.twig', [
            'investor' => $investor,
            'steps' => $steps,
            'documentCategories' => $documentCategories,
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param ObjectManager $om
     * @param Investor $investor
     *
     * @return Response
     *
     * @Route("/{investor}/edition", name="investor_edit", requirements={"investor" = "\d+"})
     */
    public function editAction(
        Request $request,
        ObjectManager $om,
        TranslatorInterface $translator,
        ContactUpdateManager $contactUpdateManager,
        Exchange $exchange,
        LoggerInterface $logger,
        $investor)
    {
        $investor = $om->getRepository('investor')->find($investor);
        $this->fillInvestorLegalEntities($investor);
        $form = $this->createForm(InvestorType::class, $investor);
        $form->handleRequest($request);


        // remove investor if no investor legal entities
        if ($form->isSubmitted() && !$investor->getInvestorLegalEntities()->count()) {
            $om->remove($investor);
            $om->flush();

            $this->addFlash('success', 'flash.investor.removed');
            return $this->redirectToRoute('investor_list');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // @TODO remove if no investor legal entities
            $om->flush();
            $this->addFlash('success', $translator->trans('success.investor.edit', [], 'SAMInvestorBundle'));
            
            if ($investor->isContact()) {
                $contactUpdateManager->flush();

                try {
                    $exchange->sync($investor->getContactMerged());
                } catch (\Exception $e) {
                    $logger->critical('An error occured on contact synchronization.', [
                        'context' => InvestorController::class,
                        'contact' => $investor->getContactMerged(),
                        'module' => 'investorController.edit',
                        'exception' => $e
                    ]);
                }
            }

            return $this->redirectToRoute('investor_show', ['investor' => $investor->getId()]);
        }

        return $this->render('@SAMInvestor/Investor/edit/index.html.twig', [
            'investor' => $investor,
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Route("/nouveau/etape-1", name="investor_create_step1")
     */
    public function createAction(Request $request)
    {
        $form = $this->createForm(InvestorImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('investor_create_step2', [
                'type' => $form->get('type')->getData(),
                'companyOrContact' => $form->get('companyOrContact')->getData()
            ]);
        }

        return $this->render('@SAMInvestor/Investor/create/step1.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param ObjectManager $om
     * @param int $type
     * @param int $companyOrContact
     *
     * @return Response
     *
     * @Route("/nouveau/etape-2/{type}/{companyOrContact}", name="investor_create_step2", requirements={"companyOrContact" = "\d+"}, defaults={"companyOrContact" = null})
     */
    public function createStep2Action(Request $request, ObjectManager $om, SearchEngineManager $searchEngineManager, $type, $companyOrContact = null)
    {
        $investor = $this->instantiateClass('investor');
        $investor->setType($type);

        if ($companyOrContact) {
            switch ($type) {
                case Investor::TYPE_LEGAL_PERSON:
                    $company = $om->getRepository('company')->find($companyOrContact);
                    $investor->setCompany($company);
                    break;
                case Investor::TYPE_NATURAL_PERSON:
                    $contact = $om->getRepository('contact_merged')->find($companyOrContact);
                    $investor->setContactMerged($contact);
                    break;
            }

            $existingInvestor = $searchEngineManager->getDoctrineRepository(InvestorRepositoryInterface::class)
                ->findExistingInvestor($investor->getCompany(), $investor->getContactMerged());
            if ($existingInvestor) {
                return $this->redirectToRoute('investor_edit', [
                    'investor' => $existingInvestor->getId()
                ]);
            }
        }

        $this->fillInvestorLegalEntities($investor);

        $form = $this->createForm(InvestorType::class, $investor);

        // preselect from previous step
        if ($name = $request->query->get('name')) {
            switch ($type) {
                case Investor::TYPE_LEGAL_PERSON:
                    $form->get('company')->get('name')->setData($name);
                    break;
                case Investor::TYPE_NATURAL_PERSON:
                    $name = explode(' ', $name);
                    $form->get('contactMerged')->get('firstName')->setData($name[0]);
                    if (isset($name[1])) {
                        $form->get('contactMerged')->get('lastName')->setData($name[1]);
                    }
                    break;
            }
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($investor->getCompany()) {
                $investor->getCompany()->setVisible(true);
            }

            $om->persist($investor);
            $om->flush();

            $this->addFlash('success', 'flash.investor.created');
            return $this->redirectToRoute('investor_list');
        }

        return $this->render('@SAMInvestor/Investor/create/step2.html.twig', [
            'form' => $form->createView(),
            'investor' => $investor,
        ]);
    }

    /**
     * @param Request        $request
     * @param ObjectManager  $om
     *
     * @return JsonResponse
     *
     * @Route("/search/boards", name="search_boards", options={"expose"=true})
     */
    public function searchBoards(Request $request, ObjectManager $om)
    {
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $boards = $om->getRepository('board')->findByName($form->get('query')->getData());

            $response = array_map(function (Board $board) {
                $returnArray = [
                    'id'    => $board->getId(),
                    'name'  => $board->getName(),
                    'text'  => $board->getName(),
                    'transform' => false
                ];

                return $returnArray;
            }, $boards);

            return new JsonResponse($response);
        }

        return new JsonResponse();
    }

    /**
     * @param string $investorId
     * @param int $id
     *
     * @return Response|JsonResponse
     *
     * @Route("/{investorId}/investor-legal-entity/{id}/remove", name="ile_remove", methods={"POST"})
     */
    public function removeInvestorLegalEntityAction($investorId, $id)
    {
        if (!$investor = $this->findEntity('investor', $investorId)) {
            throw $this->createNotFoundException();
        }

        if (!$ile = $this->findEntity('investor_legal_entity', $id)) {
            throw $this->createNotFoundException();
        }

        $investor->removeInvestorLegalEntity($ile);
        $this->entityManager->flush();
        $this->addFlash('success', 'flash.ile.removed');

        return $this->redirectToRoute('investor_edit', ['investor' => $investor->getId()]);
    }


    /**
     * @param Investor $investor
     */
    protected function fillInvestorLegalEntities(Investor $investor)
    {
        $legalEntities = $this->entityManager->getRepository('legal_entity')->findAll();

        foreach ($legalEntities as $legalEntity) {
            $investorLegalEntity = $this->instantiateClass('investor_legal_entity');
            $investorLegalEntity->setInvestor($investor);
            $investorLegalEntity->setStatus(InvestorLegalEntity::STATUS_LIMITED_PARTNER);
            $investorLegalEntity->setLegalEntity($legalEntity);

            switch ($investor->getType()) {
                case Investor::TYPE_NATURAL_PERSON:
                    $investorLegalEntity->setContactPrimary($investor->getContactMerged());
                    $investorLegalEntity->addContact($investor->getContactMerged());
                    break;
                case Investor::TYPE_LEGAL_PERSON:
                    if ($investor->getCompany()) {
                        foreach ($investor->getCompany()->getContactsMerged() as $contact) {
                            $investorLegalEntity->addContact($contact);
                        }
                    }
                    break;
            }

            $investor->addInvestorLegalEntity($investorLegalEntity);
        }
    }
}
