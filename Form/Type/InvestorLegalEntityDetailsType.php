<?php

namespace SAM\InvestorBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use SAM\InvestorBundle\Entity\Investor;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use SAM\CommonBundle\Validator\Constraints\SourcingConstraint;
use SAM\CommonBundle\Form\Type\SourcingType;
use SAM\CommonBundle\Form\Type\CardCollectionType;
use SAM\CommonBundle\Form\Type\ContactMergedCardChoiceType;
use SAM\CommonBundle\Form\Type\UserCardChoiceType;

/**
 * Class InvestorLegalEntityDetailsType
 */
class InvestorLegalEntityDetailsType extends AbstractType
{
    /** @var array */
    protected $entities;

    /** @var EntityManagerInterface */
    protected $em;

    /**
     * DealFlowType constructor.
     * @param array $entities
     * @param EntityManagerInterface $em
     */
    public function __construct($entities, EntityManagerInterface $em)
    {
        $this->entities = $entities;
        $this->em = $em;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $legalEntity = $options['legalEntity'];

        $builder
            ->add('shareCategory', EntityType::class, [
                'label' => 'category',
                'class' => $this->entities['share_category']['class'],
                'placeholder' => 'form.category.placeholder',
                'query_builder' => function (EntityRepository $er) use ($legalEntity) {
                    return $er->createQueryBuilder('sc')
                        ->where('sc.legalEntity = :legalEntity')
                        ->setParameter('legalEntity', $legalEntity);
                },
            ])
            ->add('amount', NumberType::class, ['label' => 'amount']);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->entities['investor_legal_entity_details']['class'],
            'translation_domain' => 'SAMCommonBundle'
        ]);
        $resolver->setRequired(['legalEntity']);
    }
}
