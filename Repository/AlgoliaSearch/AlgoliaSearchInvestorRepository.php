<?php

namespace SAM\InvestorBundle\Repository\AlgoliaSearch;

use SAM\InvestorBundle\Repository\InvestorRepositoryInterface;
use AppBundle\Entity\Investor;
use SAM\SearchBundle\Query\AlgoliaSearchQuery;
use SAM\SearchBundle\Repository\AbstractAlgoliaSearchRepository;
use Symfony\Component\Security\Core\User\UserInterface;

class AlgoliaSearchInvestorRepository extends AbstractAlgoliaSearchRepository implements InvestorRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function findInvestors($criteria, UserInterface $user, $page = 1)
    {
        $query = $this->getSearchQuery($criteria, $user, $page);

        return $this->paginator->paginate($query, $page, $this->itemsPerPage);
    }

    /**
     * {@inheritDoc}
     */
    public function countAll(UserInterface $user, $criteria = [])
    {
        $criterias['myInvestor'] = false;
        $query = $this->getSearchQuery($user, $criteria);

        return $this->indexManager->count($query->getQuery(), $query->getClass(), $query->getParameters());
    }

    /**
     * {@inheritDoc}
     */
    public function countMy(UserInterface $user, $criteria = [])
    {
        $criterias['myInvestor'] = true;
        $query = $this->getSearchQuery($user, $criteria);

        return $this->indexManager->count($query->getQuery(), $query->getClass(), $query->getParameters());
    }

    /**
     * {@inheritDoc}
     */
    public function getSearchQuery(UserInterface $user, $criterias = [])
    {
        $filters = [];
        $parameters = [];

        if (isset($criterias['ticket'])) {
            $min = $criterias['ticket']['min'];
            $max = $criterias['ticket']['max'];

            if (null !== $min) {
                $filters[] = sprintf("totalInvestmentAmount >= %d", $min);
            }

            if (null !== $max) {
                $filters[] = sprintf("totalInvestmentAmount <= %d", $max);
            }
        }

        if (isset($criterias['myInvestor']) && true === $criterias['myInvestor']) {
            $filters[] = sprintf('manager = %d OR users.id = %d', $user->getId(), $user->getId());
        }

        if (isset($criterias['dateRange']) && $criterias['dateRange'] instanceof DateRange) {
            $start = $criterias['dateRange'] && $criterias['dateRange']->getStart() ? $criterias['dateRange']->getStart() : null;
            $end = $criterias['dateRange'] && $criterias['dateRange']->getEnd() ? $criterias['dateRange']->getEnd() : null;

            if ($start) {
                $filters[] = sprintf("createdAt_timestamp >= %d", $start->getTimestamp());
            }
            if ($end) {
                $filters[] = sprintf("createdAt_timestamp <= %d", $end->getTimestamp());
            }
        }

        if (sizeof($filters)) {
            $parameters['filters'] = implode(' AND ', $filters);
        }

        return (new AlgoliaSearchQuery(Investor::class))
            ->setQuery($criterias['query'] ?? '')
            ->setParameters($parameters);
    }
}
