<?php

namespace SAM\InvestorBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class InvestorImportType
 */
class InvestorImportType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('companyOrContact', HiddenType::class, [
                'label' => 'label.investor.company_or_contact',
                'constraints' => [new NotBlank()]
            ])
            ->add('type', HiddenType::class, ['constraints' => [new NotBlank()]]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'SAMInvestorBundle'
        ]);
    }
}
