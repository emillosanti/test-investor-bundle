<?php

namespace SAM\InvestorBundle\Controller;

use SAM\CommonBundle\Controller\Controller;
use SAM\CommonBundle\Manager\LegalEntityManager;
use SAM\InvestorBundle\Form\Model\InvestorMetricsFilter;
use SAM\InvestorBundle\Form\Type\InvestorMetricsType;
use SAM\InvestorBundle\Manager\InvestorMetricsManager;
use Doctrine\Common\Persistence\ObjectManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class MetricsController
 *
 * @Route("/statistiques/lps")
 * @IsGranted("ROLE_ANALYTICS_USER")
 */
class MetricsController extends Controller
{
    /**
     * @param Request $request
     * @param InvestorMetricsManager $metricsManager
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @Route("/", name="metrics_investors")
     */
    public function indexAction(Request $request, InvestorMetricsManager $metricsManager, LegalEntityManager $legalEntityManager)
    {
        $investmentAmountRanges = $this->getParameter('sam_investor.analytics')['investment_amount_range_points'];
        $rangeMax = $investmentAmountRanges[count($investmentAmountRanges) - 1];

        $filter = new InvestorMetricsFilter([
            'min' => 0, 
            'max' => $rangeMax
        ], $legalEntityManager->getCurrentInvestmentLegalEntity());

        $form = $this->createForm(InvestorMetricsType::class, $filter, [
            'action' => $this->generateUrl('metrics_investors'),
            'investmentAmountRangeMax' => $rangeMax
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //
        }

        $metrics = [
            'total_investment_amount' => $metricsManager->getInvestorMetricsByTotalInvestmentAmount($filter, $investmentAmountRanges),
            'investor_category' => $metricsManager->getInvestorMetricsByCategory($filter, $investmentAmountRanges),
            'investor_legal_entity' => $metricsManager->getInvestorMetricsByLegalEntity($filter, $investmentAmountRanges),
            'investor_with_fundraiser' => $metricsManager->getInvestorMetricsByFundraiser($filter, $investmentAmountRanges),
        ];

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse($metrics);
        }

        return $this->render('@SAMInvestor/Metrics/index.html.twig', [
            'form' => $form->createView(),
            'metrics' => $metrics,
        ]);
    }

    /**
     * @param Request $request
     * @param InvestorMetricsManager $metricsManager
     *
     * @return StreamedResponse
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @Route("/export", name="metrics_investors_export")
     */
    public function exportAction(Request $request, InvestorMetricsManager $metricsManager)
    {
        $filter = new InvestorMetricsFilter();
        $form = $this->createForm(InvestorMetricsType::class, $filter);
        $form->handleRequest($request);

        $investmentAmountRanges = $this->getParameter('sam_investor.analytics')['investment_amount_range_points'];
        $writer = $metricsManager->exportMetrics($filter, $investmentAmountRanges);
        $response = new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'LPs.xlsx'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }
}
