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
        // Champ numéro de monte-charge - saisie libre pour tous
        $builder->add('numeroMonteCharge', TextType::class, [
            'label' => 'Numéro Monte-Charge',
            'attr' => [
                'class' => 'form-control',
                'placeholder' => 'Ex: MONTE CHARGE 01'
            ]
        ]);

        // Zone - liste déroulante pour tous
        $builder->add('zone', ChoiceType::class, [
            'label' => 'Zone',
            'choices' => array_flip(MonteCharge::ZONES),
            'placeholder' => 'Sélectionnez une zone',
            'attr' => [
                'class' => 'form-control',
                'id' => 'zone'
            ]
        ]);

        // Emplacement - liste déroulante pour tous
        $builder->add('emplacement', ChoiceType::class, [
            'label' => 'Emplacement',
            'choices' => array_merge(
                array_flip(MonteCharge::EMPLACEMENTS_SIMTIS),
                array_flip(MonteCharge::EMPLACEMENTS_TISSAGE)
            ),
            'placeholder' => 'Sélectionnez d\'abord une zone',
            'attr' => [
                'class' => 'form-select',
                'id' => 'emplacement'
            ]
        ]);

        // Nombre de portes - nouveau champ
        $builder->add('nombrePortes', IntegerType::class, [
            'label' => 'Nombre de Portes',
            'required' => false,
            'attr' => [
                'class' => 'form-control',
                'min' => 1,
                'max' => 10,
                'placeholder' => 'Ex: 2'
            ],
            'help' => 'Nombre total de portes du monte-charge'
        ]);

        // Numéro de porte - saisissable pour tous
        $builder->add('numeroPorte', TextType::class, [
            'label' => 'Numéro de Porte',
            'attr' => [
                'class' => 'form-control',
                'placeholder' => 'Ex: PORTE 01'
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
