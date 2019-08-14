<?php

namespace SAM\InvestorBundle\Entity;

use AppBundle\Entity\Company;
use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Evence\Bundle\SoftDeleteableExtensionBundle\Mapping\Annotation as Evence;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use SAM\AddressBookBundle\Entity\ContactMerged;

/**
 * @ORM\Table(name="investor")
 * @ORM\MappedSuperclass
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Investor
{
    use TimestampableEntity;
    use SoftDeleteableEntity;

    const TYPE_LEGAL_PERSON = 10;
    const TYPE_NATURAL_PERSON = 20;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Company
     * @Evence\onSoftDelete(type="CASCADE")
     */
    protected $company;

    /**
     * @var ContactMerged
     * @Evence\onSoftDelete(type="CASCADE")
     */
    protected $contactMerged;

    /**
     * @var int
     *
     * @ORM\Column(name="type", type="integer")
     */
    protected $type;

    /**
     * @var User
     * @Evence\onSoftDelete(type="SET NULL")
     */
    protected $creator;

    /**
     * @var InvestorCategory
     *
     * @Evence\onSoftDelete(type="SET NULL")
     */
    protected $category;

    // protected $currentStep;

    // protected $stepUpdates;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="interacted_at", type="datetime", nullable=true)
     */
    protected $interactedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="reminder_date", type="datetime", nullable=true)
     */
    protected $reminderDate;

    /**
     * @var InvestorLegalEntity[]\ArrayCollection
     */
    protected $investorLegalEntities;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_tax_benefit_activated", type="boolean")
     */
    protected $isTaxBenefitActivated = false;

    /**
     * @var float
     * 
     * @ORM\Column(name="total_investment_amount", type="decimal", precision=18, scale=2, nullable=true)
     */
    protected $totalInvestmentAmount;

    /**
     * @var float
     * 
     * @ORM\Column(name="total_investment_percentage", type="decimal", precision=5, scale=2, nullable=true)
     */
    protected $totalInvestmentPercentage;

    public function __construct()
    {
        $this->investorLegalEntities = new ArrayCollection();
        // $this->stepUpdates = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        switch ($this->getType()) {
            case self::TYPE_LEGAL_PERSON:
                return $this->getCompany() ? $this->getCompany()->getName() : null;
            case self::TYPE_NATURAL_PERSON:
                return $this->getContactMerged() ? $this->getContactMerged()->getFullName() : null;
            default:
                return null;
        }
    }

    /**
     * @return InvestorCategory|null
     */
    public function getCategory(): ?InvestorCategory
    {
        return $this->category;
    }

    /**
     * @param InvestorCategory|null $category
     * @return Investor
     */
    public function setCategory($category): Investor
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return Company|null
     */
    public function getCompany(): ?Company
    {
        return $this->company;
    }

    /**
     * @param Company $company
     * @return Investor
     */
    public function setCompany(Company $company): Investor
    {
        $this->company = $company;
        return $this;
    }

    /**
     * @return ContactMerged|null
     */
    public function getContactMerged(): ?ContactMerged
    {
        return $this->contactMerged;
    }

    /**
     * @param ContactMerged $contactMerged
     * @return Investor
     */
    public function setContactMerged(ContactMerged $contactMerged): Investor
    {
        $this->contactMerged = $contactMerged;
        return $this;
    }

    /**
     * @return int
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return Investor
     */
    public function setType(int $type): Investor
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param User $creator
     * @return Investor
     */
    public function setCreator(User $creator): Investor
    {
        $this->creator = $creator;
        return $this;
    }

    // /**
    //  * @return InvestorStep
    //  */
    // public function getCurrentStep()
    // {
    //     return $this->currentStep;
    // }

    // /**
    //  * @param InvestorStep $currentStep
    //  *
    //  * @return $this
    //  */
    // public function setCurrentStep($currentStep)
    // {
    //     $this->currentStep = $currentStep;

    //     return $this;
    // }

    // *
    //  * @return InvestorStepUpdate[]|ArrayCollection
     
    // public function getStepUpdates()
    // {
    //     return $this->stepUpdates;
    // }

    // /**
    //  * @param InvestorStepUpdate $stepUpdate
    //  *
    //  * @return $this
    //  */
    // public function addStepUpdate($stepUpdate)
    // {
    //     $stepUpdate->setInvestor($this);
    //     $this->stepUpdates->add($stepUpdate);

    //     return $this;
    // }
    
    /**
     * @return bool
     */
    public function isTaxBenefitActivated(): bool
    {
        return $this->isTaxBenefitActivated;
    }

    /**
     * @param bool $isNotified
     * @return Investor
     */
    public function setIsTaxBenefitActivated(bool $isTaxBenefitActivated): Investor
    {
        $this->isTaxBenefitActivated = $isTaxBenefitActivated;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getInteractedAt()
    {
        return $this->interactedAt;
    }

    /**
     * @param \DateTime $interactedAt
     * @return Investor
     */
    public function setInteractedAt(\DateTime $interactedAt): Investor
    {
        $this->interactedAt = $interactedAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getReminderDate()
    {
        return $this->reminderDate;
    }

    /**
     * @param \DateTime $reminderDate
     *
     * @return $this
     */
    public function setReminderDate(\DateTime $reminderDate): Investor
    {
        $this->reminderDate = $reminderDate;

        return $this;
    }

    /**
     * @return array
     */
    public static function getTypeChoices()
    {
        return [
            'investor.type.legal_person'   => self::TYPE_LEGAL_PERSON,
            'investor.type.natural_person' => self::TYPE_NATURAL_PERSON,
        ];
    }

    /**
     * @return string
     */
    public function getTypeAsString()
    {
        $choices = array_flip($this->getTypeChoices());
        if (isset($choices[$this->getType()])) {
            return $choices[$this->getType()];
        }

        return null;
    }

    /**
     * @return InvestorLegalEntity[]
     */
    public function getInvestorLegalEntities()
    {
        return $this->investorLegalEntities;
    }

    /**
     * @param InvestorLegalEntity $investorLegalEntity
     * @return Investor
     */
    public function addInvestorLegalEntity(InvestorLegalEntity $investorLegalEntity)
    {
        $this->investorLegalEntities->add($investorLegalEntity);

        return $this;
    }

    /**
     * @param InvestorLegalEntity $investorLegalEntity
     * @return Investor
     */
    public function removeInvestorLegalEntity(InvestorLegalEntity $investorLegalEntity)
    {
        $this->investorLegalEntities->removeElement($investorLegalEntity);

        return $this;
    }

    /**
     * @return float
     */
    public function getTotalInvestmentAmount()
    {
        return $this->totalInvestmentAmount;
    }

    /**
     * @param float $totalInvestmentAmount
     *
     * @return $this
     */
    public function setTotalInvestmentAmount($totalInvestmentAmount)
    {
        $this->totalInvestmentAmount = $totalInvestmentAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotalInvestmentPercentage()
    {
        return $this->totalInvestmentPercentage;
    }

    /**
     * @param float $totalInvestmentPercentage
     *
     * @return $this
     */
    public function setTotalInvestmentPercentage($totalInvestmentPercentage)
    {
        $this->totalInvestmentPercentage = $totalInvestmentPercentage;

        return $this;
    }
    
    public function getInvestorLegalEntityUsers()
    {
        $users = new ArrayCollection();

        foreach ($this->getInvestorLegalEntities() as $investorLegalEntity) {
            /** @var InvestorLegalEntity $item */
            foreach ($investorLegalEntity->getUsers() as $user) {
                if (!$users->contains($user)) {
                    $users->add($user);
                }
            }
        }

        return $users;
    }
    
    public function getBoards()
    {
        $boards = new ArrayCollection();

        foreach ($this->getInvestorLegalEntities() as $investorLegalEntity) {
            /** @var InvestorLegalEntity $item */
            foreach ($investorLegalEntity->getBoards() as $board) {
                if (!$boards->contains($board)) {
                    $boards->add($board);
                }
            }
        }

        return $boards;
    }

    public function isCompany()
    {
        return $this->getType() === self::TYPE_LEGAL_PERSON;
    }

    public function isContact()
    {
        return $this->getType() === self::TYPE_NATURAL_PERSON;
    }
}
