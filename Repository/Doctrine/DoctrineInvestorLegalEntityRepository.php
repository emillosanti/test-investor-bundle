<?php

namespace SAM\InvestorBundle\Repository\Doctrine;

use Doctrine\ORM\QueryBuilder;
use SAM\InvestorBundle\Entity\Investor;
use SAM\InvestorBundle\Repository\InvestorLegalEntityRepositoryInterface;
use SAM\SearchBundle\Repository\AbstractDoctrineRepository;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class DoctrineInvestorLegalEntityRepository
 */
class DoctrineInvestorLegalEntityRepository extends AbstractDoctrineRepository implements InvestorLegalEntityRepositoryInterface
{
    /**
     * @param UserInterface $user
     * @param array         $criterias
     *
     * @return QueryBuilder
     */
    public function getSearchQuery(UserInterface $user, $criterias = [])
    {
        $qb = $this->repository->createQueryBuilder('investor_legal_entity')
            ->join('investor_legal_entity.investor', 'investor')
            ->leftJoin('investor_legal_entity.details', 'led')
            // ->join('investor.currentStep', 'step')
            ->leftJoin('investor.company', 'company')
            ->leftJoin('investor.contactMerged', 'contactMerged');

        if (!empty($criterias['query'])) {
            $query = $criterias['query'];
            if (preg_match('/^(["\']).*\1$/m', $query)) {
                $query = preg_replace('/^(\'(.*)\'|"(.*)")$/', '$2$3', $query);
                $qb
                    ->andWhere($qb->expr()->orX(
                        $qb->expr()->andX(
                            $qb->expr()->eq('investor.type', Investor::TYPE_LEGAL_PERSON),
                            $qb->expr()->eq('company.name', $query)
                        ),
                        $qb->expr()->andX(
                            $qb->expr()->eq('investor.type', Investor::TYPE_NATURAL_PERSON),
                            $qb->expr()->eq('contactMerged.fullName', $query)
                        )
                    ));
            } else {
                $query = str_replace(' ', '* ', $query) . '*';
                $qb
                    ->andWhere($qb->expr()->orX(
                        $qb->expr()->andX(
                            $qb->expr()->eq('investor.type', Investor::TYPE_LEGAL_PERSON),
                            $qb->expr()->gt('MATCH_AGAINST(company.name, :query)', 0)
                        ),
                        $qb->expr()->andX(
                            $qb->expr()->eq('investor.type', Investor::TYPE_NATURAL_PERSON),
                            $qb->expr()->gt('MATCH_AGAINST(contactMerged.fullName, :query)', 0)
                        )
                    ))
                    ->setParameter('query', $query);
            }
        }

        if (isset($criterias['sector'])) {
            $qb
                ->andWhere('company.sector = :sector')
                ->setParameter('sector', $criterias['sector']);
        }

        if (!empty($criterias['category'])) {
            $qb
                ->andWhere('investor.category = :category')
                ->setParameter('category', $criterias['category']);
        }

        if (!empty($criterias['sourcingType'])) {
            $qb
                ->join('investor_legal_entity.sourcing', 's')
                ->andWhere('s.category = :sourcingType')
                ->setParameter('sourcingType', $criterias['sourcingType']);
        }

        if (!empty($criterias['closing'])) {
            $qb
                ->andWhere('investor_legal_entity.closing = :closing')
                ->setParameter('closing', $criterias['closing']);
        }

        if (!empty($criterias['legalEntity'])) {
            $qb
                ->andWhere('investor_legal_entity.legalEntity = :legalEntity')
                ->setParameter('legalEntity', $criterias['legalEntity']);
        }

        if (isset($criterias['totalInvestmentAmount'])) {
            $min = $criterias['totalInvestmentAmount']['min'];
            $max = $criterias['totalInvestmentAmount']['max'];

            if (null !== $min) {
                $qb
                    ->andWhere('(investor_legal_entity.investmentAmount) >= :minTicket OR investor_legal_entity.investmentAmount IS NULL')
                    ->setParameter('minTicket', $min);
            }

            if (null !== $max) {
                $qb
                    ->andWhere('(investor_legal_entity.investmentAmount) <= :maxTicket OR investor_legal_entity.investmentAmount IS NULL')
                    ->setParameter('maxTicket', $max);
            }
        }

        if (!empty($criterias['shareCategory'])) {
            $qb
                ->andWhere('led.id = :shareCategory')
                ->setParameter('shareCategory', $criterias['shareCategory']);
        }

        // @TODO => move step to InvestorLegalEntity
        // if (isset($criterias['step']) && count($criterias['step'])) {
        //     $qb
        //         ->andWhere('investor_legal_entity.currentStep IN(:step)')
        //         ->setParameter('step', $criterias['step']);
        // }

        if (isset($criterias['myInvestor']) && true === $criterias['myInvestor']) {
            $qb
                ->andWhere(':user MEMBER OF investor_legal_entity.users')
                ->setParameter('user', $user);
        }

        if (isset($criterias['highlight'])) {
            $qb
                ->andWhere('investor.highlight = :highlight')
                ->setParameter('highlight', true === $criterias['highlight']);
        }

        if (isset($criterias['dateRange'])) {
            $start = $criterias['dateRange'] && $criterias['dateRange']->getStart() ? $criterias['dateRange']->getStart() : null;
            $end = $criterias['dateRange'] && $criterias['dateRange']->getEnd() ? $criterias['dateRange']->getEnd() : null;

            // if (isset($criterias['step']) && count($criterias['step'])) {
            //     $qb->join($this->entities['investor_step_update']['class'], 'step_update', Join::WITH, 'step_update.investor = investor');
            // }

            if ($start) {
                // if (isset($criterias['step']) && count($criterias['step'])) {
                //     $qb
                //         ->andWhere('step_update.nextStep IN(:step) AND step_update.updateDate >= :startDate')
                //         ->setParameter('step', $criterias['step'])
                //         ->setParameter('startDate', $start);
                // } else {
                    $qb
                        ->andWhere('investor.createdAt >= :startDate')
                        ->setParameter('startDate', $start);
                // }
            }
            if ($end) {
                // if (isset($criterias['step']) && count($criterias['step'])) {
                //     $qb
                //         ->andWhere('step_update.nextStep IN(:step) AND step_update.updateDate <= :endDate')
                //         ->setParameter('step', $criterias['step'])
                //         ->setParameter('endDate', $end);
                // } else {
                    $qb
                        ->andWhere('investor.createdAt <= :endDate')
                        ->setParameter('endDate', $end);
                // }
            }
        }

        $qb->addOrderBy('investor.category', 'asc');
        $qb->addOrderBy('investor_legal_entity.investmentAmount', 'desc');

        return $qb;
    }

    /**
     * @param UserInterface $user
     * @param array $criterias
     *
     * @return array
     */
    public function searchInvestorLegalEntities(UserInterface $user, $criterias = [])
    {
        $qb = $this->getSearchQuery($user, $criterias);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countMy(UserInterface $user, $criterias = [])
    {
        $criterias['myInvestor'] = true;
        $qb = $this->getSearchQuery($user, $criterias);

        return $qb->select('COUNT(DISTINCT investor_legal_entity)')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleScalarResult();
    }

    /**
     * @return int
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countTotalActiveInvestors()
    {
        return $this->repository->createQueryBuilder('investor_legal_entity')
            ->select('COUNT(DISTINCT investor_legal_entity)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countAll(UserInterface $user, $criteria = [])
    {
        $criteria['myInvestor'] = false;
        $qb = $this->getSearchQuery($user, $criteria);

        return $qb->select('COUNT(DISTINCT investor_legal_entity)')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleScalarResult();
    }

    public function findInvestorLegalEntities($criterias, UserInterface $user, $page = 1)
    {
        $query = $this->getSearchQuery($user, $criterias);

        return $this->paginator->paginate($query, $page, $this->itemsPerPage);
    }


    /**
     * @param array $criterias
     * @param UserInterface $user
     * @return mixed
     */
    public function findInvestorLegalEntitiesWithoutPagination($criterias, UserInterface $user)
    {
        return $this->getSearchQuery($user, $criterias)->getQuery()->getResult();
    }
}
