<?php

namespace SAM\InvestorBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class ShareCategoryAdmin
 */
class ShareCategoryAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'sonata_share_category';
    protected $baseRoutePattern = 'investor-share-category';

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
            ->add('legalEntity', null, ['label' => 'Véhicule'])
            ->add('unitPrice', null, ['label' => 'Prix unitaire'])
        ;
    }

    /**
     * @param ListMapper $list
     */
    public function configureListFields(ListMapper $list)
    {
        $list
            ->add('name', null, ['label' => 'Nom'])
            ->add('legalEntity', null, ['label' => 'Véhicule'])
            ->add('unitPrice', null, ['label' => 'Prix unitaire'])
            ->add('_action', null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ]
            ])
        ;
    }
}
