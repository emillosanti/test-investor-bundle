<?php

namespace SAM\InvestorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\MappedSuperclass(repositoryClass="SAM\InvestorBundle\Repository\BoardRepository")
 * @ORM\Table(name="board")
 */
class Board
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    protected $name;

    /**
     * @var InvestorLegalEntity[]|ArrayCollection
     */
    protected $investorLegalEntities;

    /**
     * Board constructor.
     */
    public function __construct()
    {
        $this->investorLegalEntities = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getName();
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
        return $this->name;
    }

    /**
     * @TODO hack to work with card choice type, which requires this method
     * @return string
     */
    public function getFullName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getInvestorLegalEntities()
    {
        return $this->investorLegalEntities;
    }

    /**
     * @param InvestorLegalEntity $investorLegalEntity
     *
     * @return $this
     */
    public function addInvestorLegalEntity($investorLegalEntity)
    {
        $this->investorLegalEntities->add($investorLegalEntity);

        return $this;
    }

    /**
     * @param InvestorLegalEntity $investorLegalEntity
     *
     * @return $this
     */
    public function removeInvestorLegalEntity($investorLegalEntity)
    {
        $this->investorLegalEntities->removeElement($investorLegalEntity);

        return $this;
    }
}
