<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductLabelType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('shortDescription', TextareaType::class)
            ->add('version')
            ->add('lockType')
            ->add('language', ChoiceType::class, [
                'choices' => $options['languages'],
                'data' => $options['language'],
            ])
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\ProductLabel',
            'languages' => null,
            'language' => null,
        ]);
    }
    
    /**
     * TODO : Is it still usefull ?
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'paprec_catalogbundle_product_label';
    }
}
