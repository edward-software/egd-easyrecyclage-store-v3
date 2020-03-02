<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\PostalCode;
use App\Entity\Region;
use App\Entity\User;
use App\Repository\RegionRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostalCodeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('code', TextType::class, [
                'required' => true,
            ])
            ->add('city', TextType::class, [
                'required' => true,
            ])
            ->add('zone', TextType::class, [
                'required' => true,
            ])
            ->add('setUpRate', IntegerType::class, [
                'required' => true,
            ])
            ->add('rentalRate', IntegerType::class, [
                'required' => true,
            ])
            ->add('transportRate', IntegerType::class, [
                'required' => true,
            ])
            ->add('treatmentRate', IntegerType::class, [
                'required' => true,
            ])
            ->add('traceabilityRate', IntegerType::class, [
                'required' => true,
            ])
            ->add('region', EntityType::class, [
                'class' => Region::class,
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => false,
                'query_builder' => static function (RegionRepository $rr) {
                    return $rr
                        ->createQueryBuilder('r')
                        ->where('r.deleted IS NULL')
                        ->orderBy('r.name')
                    ;
                },
            ])
            ->add('userInCharge', EntityType::class, [
                'class' => User::class,
                'multiple' => false,
                'expanded' => false,
                'placeholder' => '',
                'empty_data' => null,
                'choice_label' => static function (User $user) {
                    return $user->getFirstName() . ' ' . $user->getLastName();
                },
                'required' => false,
                'query_builder' => static function (UserRepository $ur) {
                    return $ur->createQueryBuilder('u')
                        ->where('u.deleted IS NULL')
                        ->andWhere('u.roles LIKE \'%ROLE_COMMERCIAL%\'')
                        ->orderBy('u.firstName');
                },
            ])
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'data_class' => PostalCode::class,
        ]);
    }
}
