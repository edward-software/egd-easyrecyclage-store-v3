<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\CustomArea;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomAreaType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('leftContent', CKEditorType::class, [
                'config_name' => 'custom_config',
                'required' => true,
            ])
            ->add('rightContent', CKEditorType::class, [
                'config_name' => 'custom_config',
                'required' => true,
            ])
            ->add('isDisplayed', ChoiceType::class, [
                "choices" => [
                    'Non' => 0,
                    'Oui' => 1,
                ],
                "expanded" => true,
            ])
            ->add('code', ChoiceType::class, [
                "choices" => $options['codes'],
                "multiple" => false,
                "expanded" => false,
            ])
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
            'data_class' => CustomArea::class,
            'codes' => null,
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
        return 'paprec_catalogbundle_customarea';
    }
}
