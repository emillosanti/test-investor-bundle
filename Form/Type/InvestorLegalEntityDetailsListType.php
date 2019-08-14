<?php

namespace SAM\InvestorBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InvestorLegalEntityDetailsListType
 */
class InvestorLegalEntityDetailsListType extends AbstractType
{
    protected $entities;

    public function __construct($entities)
    {
        $this->entities = $entities;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'entry_type' => InvestorLegalEntityDetailsType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'prototype' => true,
                'by_reference' => false,
            ]
        );
    }

    public function getParent()
    {
        return CollectionType::class;
    }
}
