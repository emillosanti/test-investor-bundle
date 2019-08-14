<?php

namespace SAM\InvestorBundle\Repository\AlgoliaSearch;

use AppBundle\Entity\InvestorCategory;
use AppBundle\Entity\InvestorLegalEntity;
use SAM\CommonBundle\Entity\Entity\SourcingCategory;
use SAM\CommonBundle\Entity\BusinessSector;
use SAM\CommonBundle\Entity\LegalEntity;
use SAM\InvestorBundle\Entity\InvestorStep;
use SAM\InvestorBundle\Entity\ShareCategory;
use SAM\InvestorBundle\Repository\InvestorLegalEntityRepositoryInterface;
use SAM\SearchBundle\Query\AlgoliaSearchQuery;
use SAM\SearchBundle\Repository\AbstractAlgoliaSearchRepository;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class AlgoliaSearchInvestorLegalEntityRepository
 */
class AlgoliaSearchInvestorLegalEntityRepository extends AbstractAlgoliaSearchRepository implements InvestorLegalEntityRepositoryInterface
{
    /**
     * @param UserInterface $user
     * @param array $criteria
     * @return AlgoliaSearchQuery
     */
    public function getSearchQuery(UserInterface $user, $criteria = [])
    {
        $filters = [];
        $parameters = [];

        if (isset($criteria['totalInvestmentAmount'])) {
            $min = $criteria['totalInvestmentAmount']['min'];
            $max = $criteria['totalInvestmentAmount']['max'];

            if (null !== $min) {
                $filters[] = sprintf("investmentAmount >= %d", $min);
            }

            if (null !== $max) {
                $filters[] = sprintf("investmentAmount <= %d", $max);
            }
        }

        if (isset($criteria['sector']) && $criteria['sector'] instanceof BusinessSector) {
            $filters[] = sprintf('company.sector = %d', $criteria['sector']->getId());
        }

        if (isset($criteria['category']) && $criteria['category'] instanceof InvestorCategory) {
            $filters[] = sprintf('investor.category = %d', $criteria['category']->getId());
        }

        if (isset($criteria['sourcingType']) && $criteria['sourcingType'] instanceof SourcingCategory) {
            $filters[] = sprintf('sourcing.category = %d', $criteria['sourcingType']->getId());
        }

        if (isset($criteria['closing'])) {
            $filters[] = sprintf('investor.closing = %d', $criteria['sourcing']);
        }

        // if (isset($criteria['step']) && count($criteria['step'])) {
        //     $stepFilters = [];
        //     foreach ($criteria['step'] as $investorStep) {
        //         if ($investorStep instanceof InvestorStep) {
        //             $stepFilters[] = sprintf('investor.currentStep.id = %d', $investorStep->getId());
        //         }
        //     }

        //     if (count($stepFilters)) {
        //         $filters[] = '(' . implode(' OR ', $stepFilters) . ')';
        //     }
        // }

        if (isset($criteria['dateRange'])) {
            $start = $criteria['dateRange'] && $criteria['dateRange']->getStart() ? $criteria['dateRange']->getStart() : null;
            $end = $criteria['dateRange'] && $criteria['dateRange']->getEnd() ? $criteria['dateRange']->getEnd() : null;

            // $stepFilters = [];
            // if (isset($criteria['step']) && count($criteria['step'])) {
            //     foreach ($criteria['step'] as $investorStep) {
            //         if ($investorStep instanceof InvestorStep) {
            //             $stepFilters[] = sprintf(
            //                 'investorStepUpdates.nextStep.id = %d',
            //                 $investorStep->getId()
            //             );
            //         }
            //     }

            //     if (count($stepFilters)) {
            //         $filters[] = '(' . implode(' OR ', $stepFilters) . ')';
            //     }
            // }

            if ($start) {
                // if (isset($criteria['step']) && count($criteria['step'])) {
                //     $filters[] = sprintf('investorStepUpdates.updateDate_timestamp >= %d', $start->getTimestamp());
                // } else {
                    $filters[] = sprintf('investor.createdAt_timestamp >= %d', $start->getTimestamp());
                //}
            }
            if ($end) {
                // if (isset($criteria['step']) && count($criteria['step'])) {
                //     $filters[] = sprintf('investorStepUpdates.updateDate_timestamp <= %d', $end->getTimestamp());
                // } else {
                    $filters[] = sprintf('investor.createdAt_timestamp <= %d', $end->getTimestamp());
                //}
            }
        }

        if (isset($criteria['legalEntity']) && $criteria['legalEntity'] instanceof LegalEntity) {
            $filters[] = sprintf('legalEntity.id = %d', $criteria['legalEntity']->getId());
        }

        if (isset($criteria['shareCategory']) && $criteria['shareCategory'] instanceof ShareCategory) {
            $filters[] = sprintf('details.shareCategory.id = %d', $criteria['shareCategory']->getId());
        }

        if (isset($criterias['myInvestor']) && true === $criterias['myInvestor']) {
            $filters[] = sprintf('users.id = %d', $user->getId(), $user->getId());
        }

        // Order by step.position desc, refer to: app/Resources/SearchBundle/settings/investor_legal_entity-settings.json

        if (sizeof($filters)) {
            $parameters['filters'] = implode(' AND ', $filters);
        }

        return (new AlgoliaSearchQuery(InvestorLegalEntity::class))
            ->setQuery($criteria['query'] ?? '')
            ->setParameters($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function countMy(UserInterface $user, $criteria = [])
    {
        $criteria['myInvestor'] = true;
        $query = $this->getSearchQuery($user, $criteria);

        return $this->indexManager->count($query->getQuery(), $query->getClass(), $query->getParameters());
    }

    /**
     * {@inheritdoc}
     */
    public function countAll(UserInterface $user, $criteria = [])
    {
        $criteria['myInvestor'] = false;
        $query = $this->getSearchQuery($user, $criteria);

        return $this->indexManager->count($query->getQuery(), $query->getClass(), $query->getParameters());
    }

    /**
     * {@inheritdoc}
     */
    public function findInvestorLegalEntities($criteria, UserInterface $user, $page = 1)
    {
        $query = $this->getSearchQuery($user, $criteria);

        return $this->paginator->paginate($query, $page, $this->itemsPerPage);
    }
}