<?php

namespace SAM\InvestorBundle\Form\Type;

use SAM\CommonBundle\Form\Type\MinMaxType;
use SAM\InvestorBundle\Form\Model\InvestorMetricsFilter;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use SAM\CommonBundle\Form\Type\DateRangeType;

/**
 * Class InvestorMetricsType
 */
class InvestorMetricsType extends AbstractType
{
    protected $entities;

    protected $siteName;

    public function __construct($entities, $siteName)
    {
        $this->entities = $entities;
        $this->siteName = $siteName;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', EntityType::class, [
                'label' => 'label.investor.users',
                'class' => $this->entities['user']['class'],
                'placeholder' => 'Tous',
                'required' => false,
                'attr' => [ 'class' => 'metrics-user-select' ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.enabled = 1')
                        ->orderBy('u.firstName', 'ASC');
                },
            ])
            ->add('investorCategory', EntityType::class, [
                'label' => 'label.investor.category',
                'class' => $this->entities['investor_category']['class'],
                'placeholder' => 'Tous',
                'required' => false,
                'attr' => [ 'class' => 'metrics-investor-category-select' ]
            ])
            ->add('totalInvestmentAmount', MinMaxType::class, [
                'label' => 'label.investor.total_investment_amount',
                'defaultMax' => $options['investmentAmountRangeMax'],
                'required' => false,
                'attr' => [ 'class' => 'metrics-total-investment-slider' ]
            ])
            ->add('hasFundraiser', ChoiceType::class, [
                'label' => 'label.investor.has_fundraiser',
                'choices' => InvestorMetricsFilter::$fundraiserChoices,
                'data' => null,
                'required' => false,
                'attr' => [ 'class' => 'metrics-has-fundraiser-choice' ]
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
            'investmentAmountRangeMax' => 0,
            'translation_domain' => 'SAMInvestorBundle',
            'data_class' => InvestorMetricsFilter::class,
            'translation_domain' => 'SAMInvestorBundle'
        ]);
    }
}
