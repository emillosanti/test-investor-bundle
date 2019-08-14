<?php

namespace SAM\InvestorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SAM\CommonBundle\Entity\SourcingCategory;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @ORM\Entity
 */
class InvestorSourcingCategory extends SourcingCategory
{
    public function getType()
    {
        return 'LPs';
    }
}
