<?php

namespace SAM\InvestorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Evence\Bundle\SoftDeleteableExtensionBundle\Mapping\Annotation as Evence;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\MappedSuperclass
 * @ORM\Table(name="investor_legal_entity_details")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class InvestorLegalEntityDetails
{
    use TimestampableEntity;
    use SoftDeleteableEntity;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** 
     * @var InvestorLegalEntity
     * 
     * @Evence\onSoftDelete(type="CASCADE")
     */
    protected $investorLegalEntity;

    /** 
     * @var ShareCategory
     *
     * @Evence\onSoftDelete(type="SET NULL")
     */
    protected $shareCategory;

    /** 
     * @var InvestorStep
     *
     * @Evence\onSoftDelete(type="SET NULL")
     */
    protected $investorStep;

    /**
     * @var int
     *
     * @ORM\Column(name="amount", type="integer")
     */
    protected $amount;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return InvestorLegalEntity
     */
    public function getInvestorLegalEntity(): ?InvestorLegalEntity
    {
        return $this->investorLegalEntity;
    }

    /**
     * @param InvestorLegalEntity|null $investorLegalEntity
     * @return InvestorLegalEntityDetails
     */
    public function setInvestorLegalEntity($investorLegalEntity): InvestorLegalEntityDetails
    {
        $this->investorLegalEntity = $investorLegalEntity;
        return $this;
    }

    /**
     * @return ShareCategory
     */
    public function getShareCategory(): ?ShareCategory
    {
        return $this->shareCategory;
    }

    /**
     * @param ShareCategory $shareCategory
     * @return InvestorLegalEntityDetails
     */
    public function setShareCategory(ShareCategory $shareCategory): InvestorLegalEntityDetails
    {
        $this->shareCategory = $shareCategory;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     * @return InvestorLegalEntityDetails
     */
    public function setAmount(int $amount): InvestorLegalEntityDetails
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return InvestorStep
     */
    public function getInvestorStep(): InvestorStep
    {
        return $this->investorStep;
    }

    /**
     * @param InvestorStep $investorStep
     * @return InvestorLegalEntityDetails
     */
    public function setInvestorStep(InvestorStep $investorStep): InvestorLegalEntityDetails
    {
        $this->investorStep = $investorStep;
        return $this;
    }
}
