<?php

namespace SAM\InvestorBundle\Repository\Doctrine;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;
use SAM\InvestorBundle\Entity\Investor;
use SAM\InvestorBundle\Form\Model\InvestorMetricsFilter;
use SAM\InvestorBundle\Repository\InvestorRepositoryInterface;
use SAM\SearchBundle\Repository\AbstractDoctrineRepository;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class DoctrineInvestorRepository
 */
class DoctrineInvestorRepository extends AbstractDoctrineRepository implements InvestorRepositoryInterface
{
    /**
     * @param UserInterface $user
     * @param array         $criterias
     *
     * @return QueryBuilder
     */
    public function getSearchQuery(UserInterface $user, $criterias = [])
    {
        $qb = $this->createQueryBuilder('investor')
            ->join('investor.investorLegalEntities', 'le')
            ->join('le.details', 'led')
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
                ->join('investor.category', 'c')
                ->andWhere('c.id = :category')
                ->setParameter('category', $criterias['category']);
        }

        if (!empty($criterias['legalEntity'])) {
            $qb
                ->andWhere('le.id = :legalEntity')
                ->setParameter('legalEntity', $criterias['legalEntity']);
        }

        if (isset($criterias['ticket'])) {
            $min = $criterias['ticket']['min'];
            $max = $criterias['ticket']['max'];
            if (null !== $min && 0 != $min) {
                $qb
                    ->andWhere('(investor.totalInvestmentAmount) >= :minTicket OR investor.totalInvestmentAmount IS NULL')
                    ->setParameter('minTicket', $min);
            }

            if (null !== $max) {
                $qb
                    ->andWhere('(investor.totalInvestmentAmount) <= :maxTicket OR investor.totalInvestmentAmount IS NULL')
                    ->setParameter('maxTicket', $max);
            }
        }

        if (!empty($criterias['shareCategory'])) {
            $qb
                ->andWhere('led.id = :shareCategory')
                ->setParameter('shareCategory', $criterias['shareCategory']);
        }

        // @TODO switch to usrers from InvestorLegalEntity
        // if (isset($criterias['myInvestorFlow']) && true === $criterias['myInvestorFlow']) {
        //     $qb
        //         ->andWhere(':user MEMBER OF investor.users')
        //         ->setParameter('user', $user);
        // }

        if (isset($criterias['highlight'])) {
            $qb
                ->andWhere('investor.highlight = :highlight')
                ->setParameter('highlight', true === $criterias['highlight']);
        }

        if (isset($criterias['dateRange'])) {
            $start = $criterias['dateRange'] && $criterias['dateRange']->getStart() ? $criterias['dateRange']->getStart() : null;
            $end = $criterias['dateRange'] && $criterias['dateRange']->getEnd() ? $criterias['dateRange']->getEnd() : null;

            if ($start) {
                $qb
                    ->andWhere('investor.createdAt >= :startDate')
                    ->setParameter('startDate', $start);
            }
            if ($end) {
                $qb
                    ->andWhere('investor.createdAt <= :endDate')
                    ->setParameter('endDate', $end);
            }
        }

        // $qb->addOrderBy('step.position', 'desc');
        $qb->addOrderBy('investor.category', 'asc');
        $qb->addOrderBy('investor.totalInvestmentAmount', 'desc');

        return $qb;
    }

    /**
     * @param UserInterface $user
     * @param array $criterias
     *
     * @return array
     */
    public function searchInvestors(UserInterface $user, $criterias = [])
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

        return $qb->select('COUNT(DISTINCT investor)')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleScalarResult();
    }

    // /**
    //  * @param $investorStep
    //  *
    //  * @return int
    //  *
    //  */
    // public function countActiveByStep($investorStep)
    // {
    //     return $this->createQueryBuilder('investor')
    //         ->select('COUNT(DISTINCT investor)')
    //         ->join('investor.currentStep', 'step')
    //         ->andWhere('step.id = :stepId')
    //         ->setParameter('stepId', $investorStep->getId())
    //         ->getQuery()
    //         ->getSingleScalarResult();
    // }

    /**
     * @return int
     *
     */
    public function countTotalActiveInvestors()
    {
        return $this->createQueryBuilder('investor')
            ->select('COUNT(DISTINCT investor)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countAll(UserInterface $user, $criterias = [])
    {
        $criterias['myInvestor'] = false;
        $qb = $this->getSearchQuery($user, $criterias);

        return $qb->select('COUNT(DISTINCT investor)')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleScalarResult();
    }

    /**
     * {@inheritDoc}
     */
    public function findInvestors($criterias, UserInterface $user, $page = 1)
    {
        $query = $this->getSearchQuery($user, $criterias);

        return $this->paginator->paginate($query, $page, $this->itemsPerPage);
    }

    /**
     * Sum totalinvestment amount
     * @return float
     */
    public function sumTotalInvestmentAmount()
    {
        return $this->createQueryBuilder('investor')
                ->select('SUM(investor.totalInvestmentAmount)')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleScalarResult();
    }

    /**
     * @param InvestorMetricsFilter $filter
     * @param $investmentAmountRangeMax
     * @return array
     */
    public function findInvestorsForMetrics(InvestorMetricsFilter $filter, $investmentAmountRangeMax)
    {
        $qb = $this->createQueryBuilder('investor');
        $qb = $this->addInvestmentAmountClause($qb, $filter, $investmentAmountRangeMax);
        $qb = $this->addInvestmentCategoryClause($qb, $filter);
        $qb = $this->addInvestmentLegalEntityClause($qb, $filter);
        $qb = $this->addFundraiserClause($qb, $filter);
        $qb = $this->addUserClause($qb, $filter);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param InvestorMetricsFilter $filter
     * @param $investmentAmountRangeMax
     * @return mixed
     */
    public function findInvestorsByCategory(InvestorMetricsFilter $filter, $investmentAmountRangeMax){
        $qb = $this->createQueryBuilder('investor');

        $qb->join('investor.category', 'ic')
            ->select('COUNT(DISTINCT investor) as count, ic.name as label')
            ->groupBy('ic.name');

        $qb = $this->addInvestmentAmountClause($qb, $filter, $investmentAmountRangeMax);
        $qb = $this->addInvestmentCategoryClause($qb, $filter);
        $qb = $this->addInvestmentLegalEntityClause($qb, $filter);
        $qb = $this->addFundraiserClause($qb, $filter);
        $qb = $this->addUserClause($qb, $filter);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param InvestorMetricsFilter $filter
     * @param $investmentAmountRangeMax
     * @return mixed
     */
    public function findInvestorsByLegalEntity(InvestorMetricsFilter $filter, $investmentAmountRangeMax){

        $qb = $this->em->createQueryBuilder()
            ->select('COUNT(DISTINCT investor.id) as count, le.name as label')
            ->from($this->entities['investor_legal_entity']['class'], 'ile')
            ->leftJoin('ile.legalEntity', 'le', 'WITH ile.legal_entity_id = le.id')
            ->leftJoin('ile.investor', 'investor', 'WITH ile.investor_id = investor.id')
            ->groupBy('le.id')
        ;

        $qb = $this->addInvestmentAmountClause($qb, $filter, $investmentAmountRangeMax);
        $qb = $this->addInvestmentCategoryClause($qb, $filter);
        $qb = $this->addFundraiserClause($qb, $filter);
        $qb = $this->addUserClause($qb, $filter);

        if ($filter->getLegalEntity() != null) {
            $qb->andWhere('le.id = :legalEntityId')
               ->setParameter('legalEntityId', $filter->getLegalEntity());
        }


        return $qb->getQuery()->getResult();
    }

    /**
     * @param InvestorMetricsFilter $filter
     * @param $investmentAmountRangeMax
     * @return array
     */
    public function findInvestorsByFundraiser(InvestorMetricsFilter $filter, $investmentAmountRangeMax){
        $qb = $this->createQueryBuilder('investor')
            ->select('investor.id');

        $qb = $this->addInvestmentAmountClause($qb, $filter, $investmentAmountRangeMax);
        $qb = $this->addInvestmentCategoryClause($qb, $filter);
        $qb = $this->addInvestmentLegalEntityClause($qb, $filter);
        $qb = $this->addFundraiserClause($qb, $filter);
        $qb = $this->addUserClause($qb, $filter);

        $investors = $qb->getQuery()->getArrayResult();

        $fundraiserCompanies = $this->em->createQueryBuilder()
            ->select('fc.id as id, fc.name as name, COUNT(fc.id) as count, SUM(f.feesAmount) as feesAmount')
            ->from($this->entities['fundraiser']['class'], 'f')
            ->leftJoin('f.investorLegalEntity', 'ile')
            ->leftJoin('ile.investor', 'investor')
            ->leftJoin('f.company', 'fc', 'WITH f.company = fc.id')
            ->where('fc IS NOT NULL')
            ->andWhere('investor.id IN (:investors)')
            ->setParameter('investors', array_map(function($investor) { return $investor['id']; }, $investors))
            ->groupBy('id')
            ->getQuery()
            ->getArrayResult();

        return $fundraiserCompanies;
    }

    /**
     * Find Existing investor depending of company or contact
     */
    public function findExistingInvestor($company = null, $contact = null)
    {
        $qb = $this->createQueryBuilder('investor');

        if ($company) {
            $qb->where('investor.company = :company')
                ->setParameter('company', $company);
        } else if ($contact) {
            $qb->where('investor.contactMerged = :contact')
                ->setParameter('contact', $contact);
        } else {
            return null;
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param InvestorMetricsFilter $filter
     * @return mixed
     */
    private function addUserClause(QueryBuilder $qb, InvestorMetricsFilter $filter){
        $investmentAmount = $filter->getTotalInvestmentAmount();

        if ($filter->getUser() != null) {
            $investorFilter = $this->em->createQueryBuilder()
                ->select('DISTINCT investor.id')
                ->from($this->entities['investor_legal_entity']['class'], 'ile')
                ->leftJoin('ile.legalEntity', 'le', 'WITH ile.legal_entity_id = le.id')
                ->leftJoin('ile.investor', 'investor', 'WITH ile.investor_id = investor.id')
                ->where(':user MEMBER OF ile.users')
                ->setParameter('user', $filter->getUser())
                ->getQuery()
                ->getArrayResult();

            $investors = array_map(function($investor){return $investor['id'];}, $investorFilter);

            if (count($investors) > 0) {
                $qb ->andWhere('investor.id IN (:investorByUserIds)')
                    ->setParameter('investorByUserIds', $investors);
            }

        }

        return $qb;
    }

    /**
     * @param QueryBuilder $qb
     * @param InvestorMetricsFilter $filter
     * @param $investmentAmountRangeMax
     * @return mixed
     */
    private function addInvestmentAmountClause(QueryBuilder $qb, InvestorMetricsFilter $filter, $investmentAmountRangeMax){
        $investmentAmount = $filter->getTotalInvestmentAmount();

        if ($investmentAmount['min'] != null) {
            $qb
                ->andWhere('investor.totalInvestmentAmount >= :start')
                ->setParameter('start', $investmentAmount['min']);
        }

        if ($investmentAmount['max'] != null && $investmentAmount['max'] != $investmentAmountRangeMax) {
            $qb
                ->andWhere('investor.totalInvestmentAmount <= :end')
                ->setParameter('end', $investmentAmount['max']);
        }

        return $qb;
    }

    /**
     * @param QueryBuilder $qb
     * @param InvestorMetricsFilter $filter
     * @return mixed
     */
    private function addInvestmentCategoryClause(QueryBuilder $qb, InvestorMetricsFilter $filter) {
        if ($filter->getInvestorCategory() != null) {
            $qb
                ->andWhere('investor.category = :category')
                ->setParameter('category', $filter->getInvestorCategory());
        }

        return $qb;
    }

    /**
     * @param QueryBuilder $qb
     * @param InvestorMetricsFilter $filter
     * @return mixed
     */
    private function addInvestmentLegalEntityClause(QueryBuilder $qb, InvestorMetricsFilter $filter) {

        if ($filter->getLegalEntity() != null) {
            $investorFilter = $this->em->createQueryBuilder()
                ->select('DISTINCT investor.id')
                ->from($this->entities['investor_legal_entity']['class'], 'ile')
                ->leftJoin('ile.legalEntity', 'le', 'WITH ile.legal_entity_id = le.id')
                ->leftJoin('ile.investor', 'investor', 'WITH ile.investor_id = investor.id')
                ->where('le.id = :legalEntityId')
                ->setParameter('legalEntityId', $filter->getLegalEntity())
                ->getQuery()
                ->getArrayResult();


            $investors = array_map(function($investor){return $investor['id'];}, $investorFilter);

            $qb ->andWhere('investor.id IN (:investorIds)')
                ->setParameter('investorIds', $investors);
        }

        return $qb;
    }

    /**
     * @param QueryBuilder $qb
     * @param InvestorMetricsFilter $filter
     * @param bool $includeJoin
     * @return mixed
     */
    private function addFundraiserClause(QueryBuilder $qb, InvestorMetricsFilter $filter)
    {
        if ($filter->getHasFundraiser() === null) {
            return $qb;
        }

        $fundraiserCompanies = $this->getFundraiserCompanies();
        if ($fundraiserCompanies && count($fundraiserCompanies)) {
            $fundraiserCompanies = array_map(function ($fundraiser) { return $fundraiser['id']; }, $fundraiserCompanies);
        }

        $qb->leftJoin('investor.investorLegalEntities', 'investorLegalEntity')
            ->leftJoin('investorLegalEntity.fundraiser', 'fundraiser')
            ->leftJoin('fundraiser.company', 'fundraiser_company');

        if ($filter->getHasFundraiser() == true) {
            $qb->andWhere('fundraiser_company.id IN (:fundraiserCompanies) OR fundraiser_company IS NULL')
               ->setParameter('fundraiserCompanies', $fundraiserCompanies);
        } else {
            $qb->andWhere('fundraiser_company.id NOT IN (:fundraiserCompanies) OR fundraiser_company IS NULL')
               ->setParameter('fundraiserCompanies', $fundraiserCompanies);
        }

        return $qb;
    }

    /**
     * @return array
     */
    private function getFundraiserCompanies() 
    {
        $fundraiserCompanies = $this->em->createQueryBuilder()
            ->select('fc.id as id, fc.name as name, COUNT(fc.id) as count')
            ->from($this->entities['fundraiser']['class'], 'f')
            ->leftJoin('f.company', 'fc', 'WITH f.company = fc.id')
            ->where('fc IS NOT NULL')
            ->groupBy('id')
            ->getQuery()
            ->getArrayResult();

        return $fundraiserCompanies;
    }
}
