<?php

namespace SAM\InvestorBundle\Form\Type;

use SAM\InvestorBundle\Entity\Investor;
use SAM\CommonBundle\Form\Type\MinMaxType;
use AppBundle\Entity\InvestorLegalEntity;
use AppBundle\Entity\InvestorLegalEntityDetails;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;
use SAM\CommonBundle\Form\Type\DateRangeType;
use SAM\CommonBundle\Form\Type\SearchStepType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SunCat\MobileDetectBundle\DeviceDetector\MobileDetector;

/**
 * Class InvestorSearchType
 */
class InvestorSearchType extends AbstractType
{
    protected $entities;

    protected $siteName;

    /** @var string */
    protected $locale;

    protected $mobileDetector;

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    protected $enableEnhancedSearch;

    /**
     * InvestorSearchType constructor.
     * @param $entities
     * @param $siteName
     * @param MobileDetector $mobileDetector
     * @param UrlGeneratorInterface $urlGenerator
     * @param $enableEnhancedSearch
     */
    public function __construct($entities, $siteName, $locale, MobileDetector $mobileDetector, UrlGeneratorInterface $urlGenerator, $enableEnhancedSearch)
    {
        $this->entities = $entities;
        $this->siteName = $siteName;
        $this->locale = $locale;
        $this->urlGenerator = $urlGenerator;
        $this->mobileDetector = $mobileDetector;
        $this->enableEnhancedSearch = $enableEnhancedSearch;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $layout = $this->mobileDetector->isMobile() ? 'list' : 'table';
        $builder
            ->add('query', TextType::class, [
                'label' => 'label.investor.query',
                'attr' => ['autocomplete' => $this->enableEnhancedSearch ? 'on' : 'off', 'data-url' => 'search_query_investor_legal_entity'],
                'required' => false,
            ])
            ->add('category', EntityType::class, [
                'label' => 'label.investor.category',
                'class' => $this->entities['investor_category']['class'],
                'placeholder' => 'placeholder.investor.category.all',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name');
                },
                'required' => false
            ])
            ->add('sourcingType', EntityType::class, [
                'label' => 'label.investor.sourcing_type',
                'class' => $this->entities['investor_sourcing_category']['class'],
                'placeholder' => 'placeholder.investor.sourcing_type.all',
                'required' => false,
            ])
            ->add('shareCategory', EntityType::class, [
                'label' => 'label.investor.share_category',
                'class' => $this->entities['share_category']['class'],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('sc')
                        ->orderBy('sc.legalEntity')
                        ->addOrderBy('sc.name');
                },
                'placeholder' => 'placeholder.investor.share_category.all',
                'required' => false,
            ])
            ->add('totalInvestmentAmount', MinMaxType::class, [
                'label' => 'Ticket (k€)',
                'defaultMin' => (float)$options['min_investment'],
                'defaultMax' => (float)$options['max_investment']
            ])
            ->add('sector', EntityType::class, [
                'class' => $this->entities['business_sector']['class'],
                'query_builder' => function (EntityRepository $er)
                    {
                        return $er->createQueryBuilder('s')
                                ->orderBy('s.name', 'ASC');
                    },
                'choice_label' => 'name',
                'label' => 'Secteur d\'activité',
                'placeholder' => 'Tous',
                'required' => false,
            ])
            ->add('myInvestor', ChoiceType::class, [
                'label' => false,
                'expanded' => true,
                'multiple' => false,
                'choices' => [
                    'Mes LPs' => true,
                    'LPs ' . $this->siteName => false,
                ],
                'data' => false,
            ])
            ->add('layout', ChoiceType::class, [
                'label' => false,
                'expanded' => true,
                'multiple' => false,
                'choices' => [
                    'Liste' => 'list',
                    'Tableau' => 'table',
                ],
                'data' => $layout,
            ])
            ->add('closing', ChoiceType::class, [
                'choices' => InvestorLegalEntity::getClosingChoices($this->locale),
                'placeholder' => 'placeholder.investor.closing.all',
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'method' => 'GET',
            'action' => $this->urlGenerator->generate('investor_list'),
            'translation_domain' => 'SAMInvestorBundle',
            'min_investment' => 0,
            'max_investment' => 30000,
        ]);
    }
}
