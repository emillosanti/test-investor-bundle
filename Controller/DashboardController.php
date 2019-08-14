<?php

namespace SAM\InvestorBundle\Controller;

use SAM\InvestorBundle\Repository\InvestorRepositoryInterface;
use SAM\SearchBundle\Manager\SearchEngineManager;
use SAM\CommonBundle\Form\Model\DateRange;
use SAM\CommonBundle\Controller\Controller;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class DashboardController
 *
 * @IsGranted("ROLE_INVESTOR_USER")
 */
class DashboardController extends Controller
{
    public function tilesAnalyticsAction(SearchEngineManager $searchEngineManager)
    {
        $end = new \DateTime();
        $start = (new \DateTime())->modify('-1 year');
        $start->setTime(0, 0, 0);
        $end->setTime(23, 59, 59);

        $criterias = [
            'myInvestorFlow' => false,
            'dateRange' => new DateRange($start, $end),
        ];

        $myCount = $searchEngineManager->getRepository(InvestorRepositoryInterface::class)
            ->countMy($this->getUser(), array_merge($criterias, ['myInvestorFlow' => true]));
        $allCount = $searchEngineManager->getRepository(InvestorRepositoryInterface::class)
            ->countAll($this->getUser(), array_merge($criterias, ['myInvestorFlow' => false]));

        $totalAmount = $searchEngineManager->getDoctrineRepository(InvestorRepositoryInterface::class)->sumTotalInvestmentAmount();

        $view = '@SAMInvestor/Dashboard/tiles_analytics.html.twig';
        $params = [
            'myCount' => $myCount,
            'allCount' => $allCount,
            'totalAmount' => $totalAmount,
        ];

        $response = $this->render($view, $params);
        $response->setSharedMaxAge($this->getParameter('esi_default_cache_time'));

        return $response;
    }
}
