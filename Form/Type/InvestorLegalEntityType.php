<?php

namespace SAM\InvestorBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use SAM\FundRaisingBundle\Form\Type\FundraiserType;
use SAM\InvestorBundle\Entity\InvestorLegalEntity;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use SAM\CommonBundle\Validator\Constraints\SourcingConstraint;
use SAM\CommonBundle\Form\Type\SourcingType;
use SAM\CommonBundle\Form\Type\CardCollectionType;
use SAM\CommonBundle\Form\Type\UserCardChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use SAM\AddressBookBundle\Form\Type\ContactMergedPictureChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use SAM\AddressBookBundle\Form\DataTransformer\ContactMergedToIntTransformer;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

/**
 * Class InvestorLegalEntityType
 */
class InvestorLegalEntityType extends AbstractType
{
    /** @var array */
    protected $entities;

    /** @var string */
    protected $locale;

    /** @var EntityManagerInterface */
    protected $em;

    /**
     * @var ContactMergedToIntTransformer
     */
    protected $contactTransformer;

    /**
     * InvestorLegalEntityType constructor.
     * @param $entities
     * @param $locale
     * @param EntityManagerInterface $em
     * @param ContactMergedToIntTransformer $contactTransformer
     */
    public function __construct($entities, $locale, EntityManagerInterface $em, ContactMergedToIntTransformer $contactTransformer)
    {
        $this->entities = $entities;
        $this->locale = $locale;
        $this->em = $em;
        $this->contactTransformer = $contactTransformer;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $investorLegalEntity = $event->getData();
            $form = $event->getForm();

            if ($investorLegalEntity) {
                $form->add('details', InvestorLegalEntityDetailsListType::class, [
                    'error_bubbling' => true,
                    'label' => 'form.investorLegalEntity.details.label',
                    'entry_options' => ['legalEntity' => $investorLegalEntity->getLegalEntity()]
                ]);
            }
        });

        $builder
            ->add('investmentAmount', NumberType::class, [
                'label' => 'form.investmentAmount.label',
                'attr' => [ 'class' => 'investment-amount' ]
            ])
            ->add('isInvestmentAmountOverridden', CheckboxType::class, [
                'label' => 'form.investmentAmountOverridden.label',
                'attr' => [ 'class' => 'investment-amount-overridden' ],
                'required' => false
            ])
            ->add('warrantSignedAt', DateType::class, [
                'label' => 'form.warrantSignedAt.label',
                'format' => 'dd/MM/yyyy',
                'widget' => 'single_text',
                'required' => true,
                'constraints' => [new NotBlank()],
            ])
            ->add('closing', ChoiceType::class, [
                'label' => 'form.closing.label',
                'choices' => InvestorLegalEntity::getClosingChoices($this->locale)
            ])
            ->add('sourcing', SourcingType::class, [
                 'constraints' => [new SourcingConstraint()],
                 'sourcing_category_class' => 'investor_sourcing_category'
            ])
            ->add('fundraiser', FundraiserType::class, ['required' => false])
            ->add('users', CardCollectionType::class, [
                'label' => 'form.users.label',
                'attr' => ['placeholder' => 'form.users.placeholder', 'class' => 'autocomplete-cards-wrapper'],
                'entry_type' => UserCardChoiceType::class,
                'ajax_route' => 'search_users',
                'translation_domain' => 'SAMCommonBundle'
            ])
            ->add('boards', CardCollectionType::class, [
               'label' => 'form.boards.label',
               'attr' => ['placeholder' => 'form.boards.placeholder', 'class' => 'autocomplete-cards-wrapper autocomplete-preloaded'],
               'entry_type' => BoardCardChoiceType::class,
               'ajax_route' => 'search_boards',
               'translation_domain' => 'SAMInvestorBundle',
               'autocomplete_preloaded' => true,
            ])
            ->add('contacts', CollectionType::class, [
                'label' => false,
                'allow_add' => true,
                'attr' => ['placeholder' => 'form.contacts.placeholder'],
                'allow_delete' => true,
                'entry_type' => ContactMergedPictureChoiceType::class,
                'entry_options' => [
                    'choice_label' => 'fullName'
                ],
                'by_reference' => false,
                'translation_domain' => 'SAMInvestorBundle'
            ])
            ->add('contactPrimary', HiddenType::class, ['required' => false])

        ;

        $builder->get('contactPrimary')->addModelTransformer($this->contactTransformer);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->entities['investor_legal_entity']['class'],
            'translation_domain' => 'SAMInvestorBundle'
        ]);
    }
}
