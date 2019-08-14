<?php

namespace SAM\InvestorBundle\Entity;

use AppBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Evence\Bundle\SoftDeleteableExtensionBundle\Mapping\Annotation as Evence;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\MappedSuperclass
 * @ORM\Table(name="provision")
 */
abstract class Provision
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

    /**
     * @var User
     *
     * @Evence\onSoftDelete(type="SET NULL")
     */
    protected $creator;

    /**
     * @var int
     *
     * @ORM\Column(name="percent_release", type="integer")
     */
    protected $percentRelease;

    protected $investorLegalEntity;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User|null
     */
    public function getCreator(): ?User
    {
        return $this->creator;
    }

    /**
     * @param User $creator
     * @return Provision
     */
    public function setCreator(User $creator): Provision
    {
        $this->creator = $creator;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPercentRelease(): ?int
    {
        return $this->percentRelease;
    }

    /**
     * @param int $percentRelease
     * @return Provision
     */
    public function setPercentRelease(int $percentRelease): Provision
    {
        $this->percentRelease = $percentRelease;
        return $this;
    }

    /**
     * @return InvestorLegalEntity
     */
    public function getInvestorLegalEntity(): InvestorLegalEntity
    {
        return $this->investorLegalEntity;
    }

    /**
     * @param InvestorLegalEntity $investorLegalEntity
     * @return Provision
     */
    public function setInvestorLegalEntity(InvestorLegalEntity $investorLegalEntity): Provision
    {
        $this->investorLegalEntity = $investorLegalEntity;
        return $this;
    }
}
