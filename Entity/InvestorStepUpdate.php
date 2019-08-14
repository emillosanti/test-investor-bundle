<?php

namespace SAM\InvestorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\MappedSuperclass
 * @ORM\Table(name="investor_step_update")
 */
class InvestorStepUpdate
{
    use TimestampableEntity;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    protected $investor;

    protected $previousStep;

    protected $nextStep;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_date", type="datetime")
     */
    protected $updateDate;

    protected $author;

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
    public function getInvestor()
    {
        return $this->investor;
    }

    /**
     * @param Investor $investor
     *
     * @return $this
     */
    public function setInvestor($investor)
    {
        $this->investor = $investor;

        return $this;
    }

    /**
     * @return InvestorStep
     */
    public function getPreviousStep()
    {
        return $this->previousStep;
    }

    /**
     * @param InvestorStep $previousStep
     *
     * @return $this
     */
    public function setPreviousStep($previousStep)
    {
        $this->previousStep = $previousStep;

        return $this;
    }

    /**
     * @return InvestorStep
     */
    public function getNextStep()
    {
        return $this->nextStep;
    }

    /**
     * @param InvestorStep $nextStep
     *
     * @return $this
     */
    public function setNextStep($nextStep)
    {
        $this->nextStep = $nextStep;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * @param \DateTime $updateDate
     *
     * @return $this
     */
    public function setUpdateDate(\DateTime $updateDate)
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    /**
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param User $author
     *
     * @return $this
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }
}
