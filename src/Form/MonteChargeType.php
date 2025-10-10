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
            ->add('numeroMonteCharge', TextType::class, [
                'label' => 'Numéro Monte-Charge',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: MONTE CHARGE 01, MC-001',
                    'list' => 'numeros-monte-charge-options'
                ],
                'help' => 'Saisie libre - Ex: MONTE CHARGE 01 à 10'
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
