<?php

namespace SAM\InvestorBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use SAM\CommonBundle\Form\Type\CardChoiceType;

/**
 * Class BoardCardChoiceType
 */
class BoardCardChoiceType extends AbstractType
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
        $resolver->setDefault('class', $this->entities['board']['class']);
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return CardChoiceType::class;
    }
}
