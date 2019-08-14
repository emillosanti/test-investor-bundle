<?php

namespace SAM\InvestorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use SAM\CommonBundle\Entity\LegalEntity;
use Evence\Bundle\SoftDeleteableExtensionBundle\Mapping\Annotation as Evence;

/**
 * @ORM\MappedSuperclass
 * @ORM\Table(name="share_category")
 */
class ShareCategory
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
     * @ORM\Column(name="name", type="string")
     */
    protected $name;

    /** 
     * @var LegalEntity
     *
     * @Evence\onSoftDelete(type="SET NULL")
     */
    protected $legalEntity;

    /**
     * @var float
     *
     * @ORM\Column(name="unit_price", type="decimal", precision=10, scale=2)
     */
    protected $unitPrice;

    public function __toString()
    {
        return empty($this->name) ? '' : sprintf('%s (%d €)', $this->name, $this->getUnitPrice());
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
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return ShareCategory
     */
    public function setName(string $name): ShareCategory
    {
        $this->name = $name;
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
     * @return ShareCategory
     */
    public function setLegalEntity(LegalEntity $legalEntity): ShareCategory
    {
        $this->legalEntity = $legalEntity;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getUnitPrice(): ?float
    {
        return $this->unitPrice;
    }

    /**
     * @param float $unitPrice
     * @return ShareCategory
     */
    public function setUnitPrice(float $unitPrice): ShareCategory
    {
        $this->unitPrice = $unitPrice;
        return $this;
    }
}
