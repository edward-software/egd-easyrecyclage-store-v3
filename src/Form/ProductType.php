<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('capacity')
            ->add('capacityUnit')
            ->add('folderNumber')
            ->add('dimensions', TextareaType::class)
            ->add('isEnabled', ChoiceType::class, [
                'choices' => [
                    'Non' => 0,
                    'Oui' => 1
                ],
                'expanded' => true,
            ])
            ->add('setUpPrice', TextType::class)
            ->add('rentalUnitPrice', TextType::class)
            ->add('transportUnitPrice', TextType::class)
            ->add('treatmentUnitPrice', TextType::class)
            ->add('traceabilityUnitPrice', TextType::class)
            ->add('position')
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
    
    /**
     * TODO : Is it still usefull ?
     * @return string
     */
    public function getBlockPrefix() : string
    {
        return 'paprec_catalogbundle_product';
    }
}
