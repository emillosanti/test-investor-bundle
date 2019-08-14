<?php

namespace SAM\InvestorBundle\Manager;

use SAM\InvestorBundle\Entity\Investor;
use SAM\InvestorBundle\Entity\InvestorStep;
use SAM\CommonBundle\Entity\SourcingCategory;
use SAM\CommonBundle\Manager\AbstractMetricsManager;
use SAM\InvestorBundle\Form\Model\InvestorMetricsFilter;
use SAM\InvestorBundle\Repository\InvestorRepository;
use SAM\InvestorBundle\Repository\InvestorRepositoryInterface;
use SAM\InvestorBundle\Repository\InvestorStepRepository;
use SAM\InvestorBundle\Repository\SourcingCategoryRepository;
use SAM\SearchBundle\Manager\SearchEngineManager;
use Doctrine\Common\Persistence\ObjectManager;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class InvestorMetricsManager
 */
class InvestorMetricsManager extends AbstractMetricsManager
{
    /**
     * @var InvestorRepository
     */
    private $investorRepository;

    /**
     * @var SourcingCategoryRepository
     */
    private $sourcingCategoryRepository;

    /** @var SearchEngineManager */
    private $searchEngineManager;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * InvestorMetricsManager constructor
     *
     * @param ObjectManager $om
     * @param SearchEngineManager $searchEngineManager
     */
    public function __construct(ObjectManager $om, SearchEngineManager $searchEngineManager, TranslatorInterface $translator)
    {
        $this->searchEngineManager = $searchEngineManager;
        $this->investorRepository = $this->searchEngineManager->getDoctrineRepository(InvestorRepositoryInterface::class);
        $this->sourcingCategoryRepository = $om->getRepository('sourcing_category');
        $this->translator = $translator;
    }

    /**
     * @param InvestorMetricsFilter $filter
     * @return array
     */
    public function getMetricsByOperationType(InvestorMetricsFilter $filter) {
        return $this->investorRepository->findDealMetricsByOperationType($filter);
    }

    /**
     * @param InvestorMetricsFilter $filter
     * @return Xlsx
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function exportMetrics(InvestorMetricsFilter $filter, $investmentAmountRanges)
    {
        return $this->export([
            $this->translator->trans('title.investor.total_investment_chart', [], 'SAMInvestorBundle') => $this->getInvestorMetricsByTotalInvestmentAmount($filter, $investmentAmountRanges),
            $this->translator->trans('title.investor.investor_category', [], 'SAMInvestorBundle') => $this->getInvestorMetricsByCategory($filter, $investmentAmountRanges),
            $this->translator->trans('title.investor.investor_legal_entity', [], 'SAMInvestorBundle') => $this->getInvestorMetricsByLegalEntity($filter, $investmentAmountRanges),
            $this->translator->trans('title.investor.investor_by_fundraiser', [], 'SAMInvestorBundle') => $this->getInvestorMetricsByFundraiser($filter, $investmentAmountRanges),
        ]);
    }

    /**
     * @param InvestorMetricsFilter $filter
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getDealMetricsBySourcingCategory(InvestorMetricsFilter $filter)
    {
        $metrics = [];
        /** @var SourcingCategory[] $sourcingCategories */
        $sourcingCategories = $this->sourcingCategoryRepository->findBy([], ['id' => 'asc']);
        foreach ($sourcingCategories as $sourcingCategory) {
            $metrics[] = [
                'count' => intval($this->investorRepository->countBySourcingCategory($sourcingCategory, $filter)),
                'label' => $sourcingCategory->getName(),
            ];
        }

        return $metrics;
    }

    /**
     * @param InvestorMetricsFilter $filter
     * @param array $ranges
     * @return array
     */
    public function getInvestorMetricsByTotalInvestmentAmount(InvestorMetricsFilter $filter, $ranges) 
    {

        $metrics = [];
        $rangeMax = $this->getInvestmentRangeMax($ranges);

        //build ranges from configs
        foreach ($ranges as $key => $range) {
            $metricLabel = '';

            if ($key == 0) {
                $metrics[$key] = [
                    'count' => 0,
                    'label' => ' < ' . $range,
                    'rangeMin' => 0,
                    'rangeMax' => $range,
                ];
            } elseif ($key == count($ranges) - 1) {
                $metricLabel = ' > ' . $range;
                $metricMin   = $range;
                $metricMax   = 'inf';

                $metrics[$key] = [
                    'count' => 0,
                    'label' => $ranges[$key - 1] . ' - ' . $range,
                    'rangeMin' => $ranges[$key - 1],
                    'rangeMax' => $range,
                ];

                //add also "to infinity" after last item
                $metrics[$key + 1] = [
                    'count' => 0,
                    'label' => ' > ' . $range,
                    'rangeMin' => $range,
                    'rangeMax' => 'inf',
                ];

            } else {
                $metrics[$key] = [
                    'count' => 0,
                    'label' => $ranges[$key - 1] . ' - ' . $range,
                    'rangeMin' => $ranges[$key - 1],
                    'rangeMax' => $range,
                ];
            }

        }

        $investors = $this->investorRepository->findInvestorsForMetrics($filter, $rangeMax);

        if (count($investors) > 0) {
            foreach ($investors as $investor) {
                $amount = $investor->getTotalInvestmentAmount();

                foreach ($metrics as $key => $metric) {
                    if ($key == 0 && $amount < $metric['rangeMax']) {
                        $metrics[$key]['count']++;
                    } elseif ($key == count($ranges) && $amount >= $metric['rangeMax']) {
                        $metrics[$key]['count']++;
                    } elseif ($amount >= $metric['rangeMin'] && $amount < $metric['rangeMax']) {
                        $metrics[$key]['count']++;
                    }
                }
            }

            return $metrics;
        } else {
            return [];
        }

    }

    /**
     * @param InvestorMetricsFilter $filter
     * @param array $ranges
     * @return array
     */
    public function getInvestorMetricsByCategory($filter, $ranges) 
    {
        $investors = $this->investorRepository->findInvestorsByCategory($filter, $this->getInvestmentRangeMax($ranges));

        return $investors;
    }

    /**
     * @param InvestorMetricsFilter $filter
     * @param array $ranges
     * @return array
     */
    public function getInvestorMetricsByLegalEntity($filter, $ranges) 
    {
        $investors = $this->investorRepository->findInvestorsByLegalEntity($filter, $this->getInvestmentRangeMax($ranges));

        return $investors;
    }

    /**
     * @param InvestorMetricsFilter $filter
     * @param array $ranges
     * @return array
     */
    public function getInvestorMetricsByFundraiser($filter, $ranges) 
    {
        $fundraisers = $this->investorRepository->findInvestorsByFundraiser($filter, $this->getInvestmentRangeMax($ranges));

        $fundraisersMetrics = [];
        foreach ($fundraisers as $fundraiserCompany) {
            $fundraisersMetrics[] = [ 'count' => $fundraiserCompany['feesAmount'], 'label' => $fundraiserCompany['name'] ];
        }

        return $fundraisersMetrics;
    }

    /**
     * @param array
     * @return mixed
     */
    private function getInvestmentRangeMax($ranges) {
        return $ranges[count($ranges) - 1];
    }
}
