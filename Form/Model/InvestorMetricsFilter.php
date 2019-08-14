<?php

namespace SAM\InvestorBundle\Form\Model;

use SAM\CommonBundle\Entity\LegalEntity;
use SAM\InvestorBundle\Entity\InvestorCategory;
use Symfony\Component\Security\Core\User\UserInterface;
use SAM\CommonBundle\Form\Model\DateRange;

final class InvestorMetricsFilter
{
    /** @var DateRange */
    protected $dateRange;

    /** @var UserInterface */
    protected $user;

    /** @var LegalEntity */
    protected $legalEntity;

    /** @var InvestorCategory */
    protected $investorCategory;

    /** @var array */
    protected $totalInvestmentAmount;

    /** @var boolean */
    protected $hasFundraiser;

    public static $fundraiserChoices = [
        'choice.fundraiser.yes' => true, 
        'choice.fundraiser.no' => false, 
        'choice.fundraiser.all' => null
    ];

    /**
     * InvestorMetricsFilter constructor.
     */
    public function __construct($amountRanges = null, $legalEntity = null)
    {
        if ($amountRanges != null) {
            $this->setTotalInvestmentAmount(['min' => $amountRanges['min'], 'max' => $amountRanges['max']]);
        }

        if ($legalEntity) {
            $this->legalEntity = $legalEntity;
        }
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param UserInterface $user
     * @return InvestorMetricsFilter
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return LegalEntity
     */
    public function getLegalEntity()
    {
        return $this->legalEntity;
    }

    /**
     * @return LegalEntity
     */
    public function setLegalEntity($legalEntity)
    {
        $this->legalEntity = $legalEntity;
        return $this;
    }

    /**
     * @return InvestorCategory
     */
    public function getInvestorCategory()
    {
        return $this->investorCategory;
    }

    /**
     * @param InvestorCategory $investorCategory
     * @return InvestorMetricsFilter
     */
    public function setInvestorCategory(InvestorCategory $investorCategory)
    {
        $this->investorCategory = $investorCategory;
        return $this;
    }

    /**
     * @return array
     */
    public function getTotalInvestmentAmount(){
        return $this->totalInvestmentAmount;
    }

    /**
     * @param $totalInvestmentAmount
     * @return InvestorMetricsFilter
     */
    public function setTotalInvestmentAmount($totalInvestmentAmount){
        $this->totalInvestmentAmount = $totalInvestmentAmount;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getHasFundraiser(){
        return $this->hasFundraiser;
    }

    /**
     * @param $hasFundraiser
     * @return InvestorMetricsFilter
     */
    public function setHasFundraiser($hasFundraiser){
        $this->hasFundraiser = $hasFundraiser;
        return $this;
    }
}
