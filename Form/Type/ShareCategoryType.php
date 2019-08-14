<?php

namespace SAM\InvestorBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShareCategoryType extends AbstractType
{
    /** @var array */
    protected $entities;

    /**
     * DealFlowType constructor.
     * @param array $entities
     * @param EntityManagerInterface $em
     */
    public function __construct($entities)
    {
        $this->entities = $entities;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('unitPrice')
            ->add('legalEntity');
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->entities['share_category']['class'],
            'translation_domain' => 'SAMInvestorBundle'
        ]);
    }
}
