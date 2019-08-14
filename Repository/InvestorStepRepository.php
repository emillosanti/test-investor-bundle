<?php

namespace SAM\InvestorBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class InvestorStepRepository
 */
class InvestorStepRepository extends EntityRepository
{
    /**
     * @param InvestorStep $step
     *
     * @return InvestorStep
     */
    public function findNextStep($step)
    {
        return $this->createQueryBuilder('step')
            ->where('step.position > :current')
            ->setParameter('current', $step->getPosition())
            ->orderBy('step.position', 'asc')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
