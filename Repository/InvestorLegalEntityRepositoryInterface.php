<?php

namespace SAM\InvestorBundle\Repository;

use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface InvestorLegalEntityRepositoryInterface
{
    /**
     * @param array $criteria
     * @param UserInterface $user
     * @param int $page
     * @return PaginationInterface
     */
    public function findInvestorLegalEntities($criteria, UserInterface $user, $page = 1);

    /**
     * @param UserInterface $user
     * @param array $criteria
     * @return int
     */
    public function countMy(UserInterface $user, $criteria = []);

    /**
     * @param UserInterface $user
     * @param array $criteria
     * @return int
     */
    public function countAll(UserInterface $user, $criteria = []);
}