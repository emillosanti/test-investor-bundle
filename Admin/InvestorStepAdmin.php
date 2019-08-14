<?php

namespace SAM\InvestorBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\CoreBundle\Form\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class InvestorStepAdmin
 */
class InvestorStepAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'sonata_investor_step';
    protected $baseRoutePattern = 'investor-step';

    protected $datagridValues = [
        '_page' => 1,
        '_sort_order' => 'ASC',
        '_sort_by' => 'position',
    ];

    /**
     * @param FormMapper $form
     */
    public function configureFormFields(FormMapper $form)
    {
        $form
            ->add('name', TextType::class, ['label' => 'Nom'])
            ->add('textColor', ColorType::class, ['label' => 'Couleur du texte'])
            ->add('backgroundColor', ColorType::class, ['label' => 'Couleur de fond'])
            ->add('recommendedDocuments', TextType::class, ['label' => 'Documents recommandés', 'required' => false])
        ;
    }

    /**
     * @param ListMapper $list
     */
    public function configureListFields(ListMapper $list)
    {
        $list
            ->add('name', null, ['label' => 'Nom'])
            ->add('_action', null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                    'move' => [
                        'template' => '@PixSortableBehavior/Default/_sort.html.twig'
                    ],
                ]
            ])
        ;
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('move', $this->getRouterIdParameter().'/move/{position}');
    }
}
