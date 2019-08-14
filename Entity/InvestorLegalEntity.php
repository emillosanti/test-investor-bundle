<?php

namespace SAM\InvestorBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use SAM\CommonBundle\Entity\Sourcing;
use SAM\CommonBundle\Entity\LegalEntity;
use SAM\CommonBundle\Entity\Traits\DocumentTrait;
use SAM\CommonBundle\Entity\Traits\InteractionAppointmentTrait;
use SAM\CommonBundle\Entity\Traits\InteractionCallTrait;
use SAM\CommonBundle\Entity\Traits\InteractionEmailTrait;
use SAM\CommonBundle\Entity\Traits\InteractionLetterTrait;
use SAM\CommonBundle\Entity\Traits\InteractionNoteTrait;
use SAM\CommonBundle\Entity\Traits\InteractionsTrait;
use SAM\AddressBookBundle\Entity\ContactMerged;
use SAM\FundRaisingBundle\Entity\Fundraiser;
use Evence\Bundle\SoftDeleteableExtensionBundle\Mapping\Annotation as Evence;


/**
 * @ORM\MappedSuperclass
 * @ORM\Table(name="investor_legal_entity")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class InvestorLegalEntity
{
    use InteractionAppointmentTrait;
    use InteractionCallTrait;
    use InteractionEmailTrait;
    use InteractionLetterTrait;
    use InteractionNoteTrait;
    use InteractionsTrait;
    use TimestampableEntity;
    use DocumentTrait;
    use SoftDeleteableEntity;

    const STATUS_PROSPECT = 10;
    const STATUS_LIMITED_PARTNER = 20;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Investor
     *
     * @Evence\onSoftDelete(type="CASCADE")
     */
    protected $investor;

    /** 
     * @var LegalEntity 
     *
     * @Evence\onSoftDelete(type="CASCADE")
     */
    protected $legalEntity;

    /**
     * @var Fundraiser|null
     *
     * @Evence\onSoftDelete(type="SET NULL")
     */
    protected $fundraiser;

    /** @var InvestorLegalEntityDetails|ArrayCollection */
    protected $details;

    /**
     * @var Sourcing
     *
     * @Evence\onSoftDelete(type="SET NULL")
     */
    protected $sourcing;

    /**
     * @var Board[]|ArrayCollection
     */
    protected $boards;

    /**
     * @var User[]|ArrayCollection
     */
    protected $users;

    /**
     * @ORM\Column(name="closing", type="integer", nullable=true)
     */
    protected $closing;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="warrant_signed_at", type="datetime", nullable=true)
     */
    protected $warrantSignedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sold_at", type="datetime", nullable=true)
     */
    protected $soldAt;

    /**
     * @var float
     * 
     * @ORM\Column(name="investment_amount", type="decimal", precision=18, scale=2, nullable=true)
     */
    protected $investmentAmount;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_investment_amount_overridden", type="boolean", options={"default":"0"})
     */
    protected $isInvestmentAmountOverridden;

    /**
     * @var float
     * 
     * @ORM\Column(name="investment_range_min", type="decimal", precision=18, scale=2, nullable=true)
     */
    protected $investmentRangeMin;

    /**
     * @var float
     * 
     * @ORM\Column(name="investment_range_max", type="decimal", precision=18, scale=2, nullable=true)
     */
    protected $investmentRangeMax;

    /**
     * @var float
     * 
     * @ORM\Column(name="investment_percentage", type="decimal", precision=5, scale=2, nullable=true)
     */
    protected $investmentPercentage;

    /**
     * @var string
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    protected $notes;

    protected $provisions;

    /**
     * @var ContactMerged
     */
    protected $contactPrimary;

    protected $contacts;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer")
     */
    protected $status;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->contacts = new ArrayCollection();
        $this->details = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->boards = new ArrayCollection();
        $this->provisions = new ArrayCollection();
        $this->warrantSignedAt = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Investor
     */
    public function getInvestor(): ?Investor
    {
        return $this->investor;
    }

    /**
     * @param Investor $investor
     * @return InvestorLegalEntity
     */
    public function setInvestor(Investor $investor): InvestorLegalEntity
    {
        $this->investor = $investor;
        return $this;
    }

    /**
     * @return LegalEntity|null
     */
    public function getLegalEntity(): ?LegalEntity
    {
        return $this->legalEntity;
    }

    /**
     * @param LegalEntity $legalEntity
     * @return InvestorLegalEntity
     */
    public function setLegalEntity(LegalEntity $legalEntity): InvestorLegalEntity
    {
        $this->legalEntity = $legalEntity;
        return $this;
    }

    /**
     * @return Fundraiser
     */
    public function getFundraiser(): ?Fundraiser
    {
        return $this->fundraiser;
    }

    /**
     * @param Fundraiser $fundraiser
     * @return InvestorLegalEntity
     */
    public function setFundraiser(Fundraiser $fundraiser): InvestorLegalEntity
    {
        $this->fundraiser = $fundraiser;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getBoards()
    {
        return $this->boards;
    }

    /**
     * @param Board $board
     *
     * @return $this
     */
    public function addBoard($board)
    {
        $this->boards->add($board);

        return $this;
    }

    /**
     * @param Board $board
     *
     * @return $this
     */
    public function removeBoard($board)
    {
        $this->boards->removeElement($board);

        return $this;
    }

    /**
     * @return User[]|ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param User[]|ArrayCollection $users
     * @return Investor
     */
    public function setUsers($users)
    {
        $this->users = $users;
        return $this;
    }

    /**
     * @return int
     */
    public function getClosing()
    {
        return $this->closing;
    }

    /**
     * @param int $closing
     * @return InvestorLegalEntity
     */
    public function setClosing(int $closing): InvestorLegalEntity
    {
        $this->closing = $closing;
        return $this;
    }

    /**
     * @return ArrayCollection|InvestorLegalEntityDetails
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param ArrayCollection|InvestorLegalEntityDetails $details
     */
    public function setDetails($details): void
    {
        $this->details = $details;
    }

    /**
     * @param InvestorLegalEntityDetails $detail
     * @return InvestorLegalEntity
     */
    public function addDetail(InvestorLegalEntityDetails $detail)
    {
        $this->details->add($detail);
        $detail->setInvestorLegalEntity($this);

        return $this;
    }

    /**
     * @param InvestorLegalEntityDetails $detail
     * @return InvestorLegalEntity
     */
    public function removeDetail(InvestorLegalEntityDetails $detail)
    {
        $this->details->removeElement($detail);
        $detail->setInvestorLegalEntity(null);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getWarrantSignedAt()
    {
        return $this->warrantSignedAt;
    }

    /**
     * @param \DateTime|null $warrantSignedAt
     * @return InvestorLegalEntity
     */
    public function setWarrantSignedAt($warrantSignedAt): InvestorLegalEntity
    {
        $this->warrantSignedAt = $warrantSignedAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getSoldAt()
    {
        return $this->soldAt;
    }

    /**
     * @param \DateTime $soldAt
     * @return InvestorLegalEntity
     */
    public function setSoldAt(\DateTime $soldAt): Investor
    {
        $this->soldAt = $soldAt;
        return $this;
    }

    /**
     * @return float
     */
    public function getInvestmentAmount()
    {
        return $this->investmentAmount;
    }

    /**
     * @param float $investmentAmount
     *
     * @return $this
     */
    public function setInvestmentAmount($investmentAmount)
    {
        $this->investmentAmount = $investmentAmount;

        return $this;
    }

    /**
     * @return bool
     */
    public function isInvestmentAmountOverridden(): ?bool
    {
        return $this->isInvestmentAmountOverridden;
    }

    /**
     * @param bool $isInvestmentAmountOverridden
     * @return InvestorLegalEntity
     */
    public function setIsInvestmentAmountOverridden(bool $isInvestmentAmountOverridden): ?InvestorLegalEntity
    {
        $this->isInvestmentAmountOverridden = $isInvestmentAmountOverridden;
        return $this;
    }

    /**
     * @return float
     */
    public function getInvestmentPercentage()
    {
        return $this->investmentPercentage;
    }

    /**
     * @param float $investmentPercentage
     *
     * @return $this
     */
    public function setInvestmentPercentage($investmentPercentage)
    {
        $this->investmentPercentage = $investmentPercentage;

        return $this;
    }

    /**
     * @return Sourcing|null
     */
    public function getSourcing(): ?Sourcing
    {
        return $this->sourcing;
    }

    /**
     * @param Sourcing $sourcing
     * @return Investor
     */
    public function setSourcing(Sourcing $sourcing): InvestorLegalEntity
    {
        $sourcing->setInvestorLegalEntity($this);
        $this->sourcing = $sourcing;
        return $this;
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param string $notes
     *
     * @return $this
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getProvisions()
    {
        return $this->provisions;
    }

    /**
     * @param Provision $provision
     * @return InvestorLegalEntity
     */
    public function addProvision(Provision $provision)
    {
        $this->provisions->add($provision);

        return $this;
    }

    /**
     * @param Provision $provision
     * @return InvestorLegalEntity
     */
    public function removeProvision(Provision $provision)
    {
        $this->provisions->removeElement($provision);

        return $this;
    }

    /**
     * @return ContactMerged
     */
    public function getContactPrimary()
    {
        return $this->contactPrimary;
    }

    /**
     * @param ContactMerged|null $contactPrimary
     * @return Investor
     */
    public function setContactPrimary($contactPrimary): InvestorLegalEntity
    {
        $this->contactPrimary = $contactPrimary;

        return $this;
    }

    /**
     * @return array
     */
    public static function getClosingChoices($locale)
    {
        $formatter = new \NumberFormatter($locale, \NumberFormatter::ORDINAL);
        $choices = [];
        for ($i = 1; $i <= 10; $i++) {
            $choices[$formatter->format($i)] = $i;
        }

        return $choices;
    }

    /**
     * @return Collection|ContactMerged[]
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * @param ContactMerged[]|Collection $contacts
     * @return Investor
     */
    public function setContacts($contacts)
    {
        $this->contacts = $contacts;

        return $this;
    }

    /**
     * @param ContactMerged $contact
     *
     * @return $this
     */
    public function addContact($contact)
    {
        $this->contacts->add($contact);

        return $this;
    }

    /**
     * @param ContactMerged $contact
     *
     * @return $this
     */
    public function removeContact($contact)
    {
        $this->contacts->removeElement($contact);

        return $this;
    }

    /**
     * @return float
     */
    public function getInvestmentRangeMin()
    {
        return $this->investmentRangeMin;
    }

    /**
     * @param float $investmentRangeMin
     *
     * @return $this
     */
    public function setInvestmentRangeMin($investmentRangeMin)
    {
        $this->investmentRangeMin = $investmentRangeMin;

        return $this;
    }

    /**
     * @return float
     */
    public function getInvestmentRangeMax()
    {
        return $this->investmentRangeMax;
    }

    /**
     * @param float $investmentRangeMax
     *
     * @return $this
     */
    public function setInvestmentRangeMax($investmentRangeMax)
    {
        $this->investmentRangeMax = $investmentRangeMax;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return Investor
     */
    public function setStatus(int $status): InvestorLegalEntity
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return array
     */
    public static function getStatusChoices()
    {
        return [
            'investor_legal_entity.status.prospect'   => self::STATUS_PROSPECT,
            'investor_legal_entity.status.limited_partner' => self::STATUS_LIMITED_PARTNER,
        ];
    }

    /**
     * @return string
     */
    public function getStatusAsString()
    {
        $choices = array_flip($this->getStatusChoices());
        if (isset($choices[$this->getStatus()])) {
            return $choices[$this->getStatus()];
        }

        return null;
    }
}
