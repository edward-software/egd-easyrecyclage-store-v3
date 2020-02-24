<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\QuoteRequest;
use App\Entity\User;
use App\Form\DataTransformer\PostalCodeToStringTransformer;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuoteRequestType extends AbstractType
{
    /** @var PostalCodeToStringTransformer */
    private $transformer;
    
    /**
     * QuoteRequestType constructor.
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
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('locale', ChoiceType::class, [
                'choices' => $options['locales'],
            ])
            ->add('canton')
            ->add('businessName')
            ->add('civility', ChoiceType::class, [
                'choices' => [
                    'M' => 'M',
                    'Mme' => 'Mme',
                ],
                'expanded' => true,
            ])
            ->add('access', ChoiceType::class, [
                'choices' => $options['access'],
                'choice_label' => static function ($choiceValue) {
                    return 'Commercial.AccessList.' . $choiceValue;
                },
            ])
            ->add('staff', ChoiceType::class, [
                'choices' => $options['staff'],
                'choice_label' => static function ($choiceValue) {
                    return 'Commercial.StaffList.' . $choiceValue;
                },
            ])
            ->add('lastName', TextType::class)
            ->add('firstName', TextType::class)
            ->add('email', TextType::class)
            ->add('phone', TextType::class)
            ->add('isMultisite', ChoiceType::class, [
                'choices' => [0, 1],
                'choice_label' => static function ($choiceValue) {
                    return 'General.' . $choiceValue;
                },
                'expanded' => false,
            ])
            ->add('address', TextType::class)
            ->add('postalCode', TextType::class, [
                'invalid_message' => 'Public.Contact.PostalCodeError',
            ])
            ->add('city', TextType::class)
            ->add('comment', TextareaType::class)
            ->add('quoteStatus', ChoiceType::class, [
                'choices' => $options['status'],
                'choice_label' => static function ($choiceValue) {
                    return 'Commercial.QuoteStatusList.' . $choiceValue;
                }
            ])
            ->add('overallDiscount')
            ->add('salesmanComment', TextareaType::class)
            ->add('annualBudget')
            ->add('frequency')
            ->add('frequency', ChoiceType::class, [
                'choices' => [
                    'Regular' => 'regular',
                    'Ponctual' => 'ponctual',
                ],
                'choice_label' => static function ($choiceValue) {
                    return 'Commercial.QuoteRequest.' . ucfirst($choiceValue);
                },
                'expanded' => true,
            ])
            ->add('frequencyTimes', ChoiceType::class, [
                'choices' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                    '7' => '7',
                    '8' => '8',
                    '9' => '9',
                    '10' => '10',
                ],
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('frequencyInterval', ChoiceType::class, [
                'choices' => [
                    'week' => 'week',
                    'quarter' => 'quarter',
                    'year' => 'year',
                ],
                'choice_label' => static function ($choiceValue) {
                    return 'Public.Catalog.' . ucfirst($choiceValue);
                },
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('reference')
            ->add('customerId')
            ->add('userInCharge', EntityType::class, [
                'class' => User::class,
                'multiple' => false,
                'expanded' => false,
                'placeholder' => '',
                'empty_data' => null,
                'choice_label' => static function (User $user) {
                    return $user->getFirstName() . ' ' . $user->getLastName();
                },
                'query_builder' => static function (UserRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.deleted IS NULL')
                        ->orderBy('u.firstName');
                },
            ])
        ;
    
        $builder
            ->get('postalCode')
            ->addModelTransformer($this->transformer)
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'data_class' => QuoteRequest::class,
            'validation_groups' => static function (FormInterface $form) {
                $data = $form->getData();
                if ($data->getIsMultisite() === 1) {
                    
                    return ['default', 'public'];
                }
                
                return ['default', 'public', 'public_multisite'];
            },
            'status' => null,
            'locales' => null,
            'staff' => null,
            'access' => null,
        ]);
    }
    
    /**
     * TODO : Is it still usefull ?
     * @return string
     */
    public function getBlockPrefix() : string
    {
        return 'paprec_catalogbundle_quote_request';
    }
}
