<?php

namespace SAM\InvestorBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\CoreBundle\Form\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Sonata\AdminBundle\Form\Type\ModelListType;

/**
 * Class BoardAdmin
 */
class BoardAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'sonata_board';
    protected $baseRoutePattern = 'board';

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
