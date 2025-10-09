<?php

namespace App\Form;

use App\Entity\MonteCharge;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MonteChargeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numeroMonteCharge', ChoiceType::class, [
                'label' => 'Numéro Monte-Charge',
                'choices' => array_flip(MonteCharge::NUMEROS_MONTE_CHARGE),
                'placeholder' => 'Sélectionnez un numéro',
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('zone', ChoiceType::class, [
                'label' => 'Zone',
                'choices' => array_flip(MonteCharge::ZONES),
                'placeholder' => 'Sélectionnez une zone',
                'attr' => [
                    'class' => 'form-select',
                    'id' => 'monte_charge_zone'
                ]
            ])
            ->add('emplacement', TextType::class, [
                'label' => 'Emplacement',
                'attr' => [
                    'class' => 'form-control',
                    'list' => 'emplacement-monte-charge-options',
                    'id' => 'monte_charge_emplacement',
                    'placeholder' => 'Sélectionner un emplacement'
                ]
            ])
            ->add('numeroPorte', ChoiceType::class, [
                'label' => 'Numéro de Porte',
                'choices' => array_flip(MonteCharge::NUMEROS_PORTE),
                'placeholder' => 'Sélectionnez une porte',
                'attr' => [
                    'class' => 'form-select'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MonteCharge::class,
        ]);
    }
}
