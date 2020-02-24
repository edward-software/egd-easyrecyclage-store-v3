<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\Picture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PictureProductType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('path', FileType::class, [
                'data_class' => null,
                'attr' => [
                    'accept' => 'image/*',
                ],
            ])
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'data_class' => Picture::class,
            'types' => null,
        ]);
    }
    
    /**
     * TODO : Is it still usefull ?
     * @return string
     */
    public function getBlockPrefix() : string
    {
        return 'paprec_catalogbundle_picture';
    }
}
