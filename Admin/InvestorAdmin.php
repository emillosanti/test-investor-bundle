<?php

namespace SAM\InvestorBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;

/**
 * Class InvestorAdmin
 */
class InvestorAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'sonata_investor';
    protected $baseRoutePattern = 'investor';

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
            ->add('category', ModelType::class, [
                'label' => 'Category',
                'property'  => 'name',
                'expanded' => false,
                'multiple' => false,
            ])
        ;
    }

    /**
     * @param ListMapper $list
     */
    public function configureListFields(ListMapper $list)
    {
        $list
            ->add('category', null, ['label' => 'Category'])
            ->add('_action', null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ]
            ])
        ;
    }
}
