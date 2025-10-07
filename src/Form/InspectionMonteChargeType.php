<?php

namespace App\Form;

use App\Entity\InspectionMonteCharge;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class InspectionMonteChargeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('portesFermees', ChoiceType::class, [
                'label' => InspectionMonteCharge::QUESTIONS['portes_fermees'],
                'choices' => array_flip(InspectionMonteCharge::REPONSES),
                'placeholder' => 'Sélectionnez une réponse',
                'attr' => [
                    'class' => 'form-control'
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('consignesRespectees', ChoiceType::class, [
                'label' => InspectionMonteCharge::QUESTIONS['consignes_respectees'],
                'choices' => array_flip(InspectionMonteCharge::REPONSES),
                'placeholder' => 'Sélectionnez une réponse',
                'attr' => [
                    'class' => 'form-control'
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('finsCoursesFonctionnent', ChoiceType::class, [
                'label' => InspectionMonteCharge::QUESTIONS['fins_courses_fonctionnent'],
                'choices' => array_flip(InspectionMonteCharge::REPONSES),
                'placeholder' => 'Sélectionnez une réponse',
                'attr' => [
                    'class' => 'form-control'
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('essaiVideRealise', ChoiceType::class, [
                'label' => InspectionMonteCharge::QUESTIONS['essai_vide_realise'],
                'choices' => array_flip(InspectionMonteCharge::REPONSES),
                'placeholder' => 'Sélectionnez une réponse',
                'attr' => [
                    'class' => 'form-control'
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('observations', TextareaType::class, [
                'label' => 'Observations (optionnel)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Ajoutez des observations si nécessaire...'
                ]
            ])
            ->add('photoObservation', FileType::class, [
                'label' => 'Photo d\'observation (optionnel)',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp'
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG, GIF, WebP)',
                        'maxSizeMessage' => 'La taille du fichier ne doit pas dépasser 5MB'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InspectionMonteCharge::class,
        ]);
    }
}
