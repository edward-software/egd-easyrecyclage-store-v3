<?php

namespace App\Form;

use App\Form\DataTransformer\PostalCodeToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuoteRequestPublicType extends AbstractType
{
    /** @var PostalCodeToStringTransformer */
    private $transformer;
    
    /**
     * QuoteRequestPublicType constructor.
     *
     * @param PostalCodeToStringTransformer $transformer
     */
    public function __construct(PostalCodeToStringTransformer $transformer)
    {
        $this->transformer = $transformer;
    }
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('canton')
            ->add('businessName')
            ->add('civility', ChoiceType::class, [
                'choices' => [
                    'M' => 'M',
                    'Mme' => 'Mme',
                ],
                'expanded' => true
            ])
            ->add('access', ChoiceType::class, [
                "choices" => $options['access'],
                "choice_label" => function ($choiceValue, $key, $value) {
                    return 'Commercial.AccessList.' . $choiceValue;
                },
                'data' => 'stairs',
                'required' => true
            ])
            ->add('staff', ChoiceType::class, [
                "choices" => $options['staff'],
                "choice_label" => function ($choiceValue, $key, $value) {
                    return 'Commercial.StaffList.' . $choiceValue;
                },
                'data' => '19',
                'required' => true
            ])
            ->add('lastName', TextType::class)
            ->add('firstName', TextType::class)
            ->add('email', TextType::class)
            ->add('phone', TelType::class, [
                'invalid_message' => 'Public.Contact.PhoneError',
            ])
            ->add('isMultisite', ChoiceType::class, [
                "choices" => [0, 1],
                "choice_label" => function ($choiceValue, $key, $value) {
                    return 'General.' . $choiceValue;
                },
                "data" => 0,
                "expanded" => true,
            ])
            ->add('address', TextType::class)
            ->add('postalCode', TextType::class, [
                'invalid_message' => 'Public.Contact.PostalCodeError'
            ])
            ->add('city', TextType::class)
            ->add('comment', TextareaType::class)
        ;
        
        $builder
            ->get('postalCode')
            ->addModelTransformer($this->transformer)
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\QuoteRequest',
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                if ($data->getIsMultisite() === 1) {
                    return ['public'];
                }
                
                return ['public', 'public_multisite'];
            },
            'access' => null,
            'staff' => null,
            'locale' => null,
        ]);
    }
    
    /**
     * TODO : Is it still usefull ?
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'paprec_catalogbundle_quote_request_public';
    }
}
