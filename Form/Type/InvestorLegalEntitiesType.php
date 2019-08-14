<?php

namespace SAM\InvestorBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InvestorLegalEntitiesType
 */
class InvestorLegalEntitiesType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'entry_type' => InvestorLegalEntityType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'prototype' => true,
                'by_reference' => false,
            ]
        );
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}
