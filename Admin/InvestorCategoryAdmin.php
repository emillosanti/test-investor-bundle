<?php

namespace SAM\InvestorBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Sonata\CoreBundle\Form\Type\ColorType;

/**
 * Class InvestorCategoryAdmin
 */
class InvestorCategoryAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'sonata_investor_category';
    protected $baseRoutePattern = 'investor-category';

    protected $datagridValues = [
        '_page' => 1,
        '_sort_order' => 'ASC',
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
                ]
            ])
        ;
    }
}
