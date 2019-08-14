<?php

namespace SAM\InvestorBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use SAM\AddressBookBundle\Form\Type\ContactMergedType;
use SAM\InvestorBundle\Entity\Investor;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use SAM\AddressBookBundle\Form\Type\CompanyType;

use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class InvestorType
 */
class InvestorType extends AbstractType
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
        $builder
            ->add('investorLegalEntities', InvestorLegalEntitiesType::class, [
                'label' => false,
                'constraints' => [new Count(['min' => 1, 'minMessage' => 'validator.investorLegalEntities.min'])]
            ])
            ->add('category', EntityType::class, [
                'class' => $this->entities['investor_category']['class'],
                'label' => 'label.investor.category',
                'constraints' => [new NotBlank()],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name');
                },
                'placeholder' => 'placeholder.investor.category',
            ])
            ->add('type', ChoiceType::class,
                [
                    'label' => 'label.investor.type',
                    'choices' => Investor::getTypeChoices()
                ]
            )
            ->add('isTaxBenefitActivated', CheckboxType::class, [
                'label' => 'form.isTaxBenefitActivated.label',
                'required' => false
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();

            if ($event->getData()->isCompany()) {
                $form->add('company', CompanyType::class);
            } else {
                $form->add('contactMerged', ContactMergedType::class);
            }
        });
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->entities['investor']['class'],
            'translation_domain' => 'SAMInvestorBundle'
        ]);
    }
}
