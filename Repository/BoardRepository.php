<?php

namespace SAM\InvestorBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class BoardRepository
 */
class BoardRepository extends EntityRepository
{
    /**
     * @param string $query
     *
     * @return array
     */
    public function findByName($query)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.name LIKE :query')
            ->setParameters(['query' => $query . '%']);

        return $qb->getQuery()->getResult();
    }
}