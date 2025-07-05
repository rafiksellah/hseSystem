<?php

namespace App\Form;

use App\Entity\RapportHSE;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UserRapportHSEType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champs en lecture seule pour l'utilisateur connecté
            ->add('codeAgt', TextType::class, [
                'label' => 'Code AGT',
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true,
                    'style' => 'background-color: #e9ecef;'
                ]
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom complet',
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true,
                    'style' => 'background-color: #e9ecef;'
                ]
            ])
            ->add('date', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true,
                    'style' => 'background-color: #e9ecef;'
                ]
            ])
            ->add('heure', TimeType::class, [
                'label' => 'Heure',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true,
                    'style' => 'background-color: #e9ecef;'
                ]
            ])
            ->add('zone', ChoiceType::class, [
                'label' => 'Zone',
                'choices' => [
                    'Zone A' => 'Zone A',
                    'Zone B' => 'Zone B',
                    'Zone C' => 'Zone C',
                    'Zone Production' => 'Zone Production',
                    'Zone Stockage' => 'Zone Stockage',
                    'Zone Administrative' => 'Zone Administrative',
                    'Autres' => 'Autres'
                ],
                'placeholder' => 'Sélectionnez une zone',
                'required' => false,
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('emplacement', TextType::class, [
                'label' => 'Emplacement',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Emplacement précis'
                ]
            ])
            ->add('equipementProduitConcerne', TextType::class, [
                'label' => 'Équipement/Produit concerné',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Équipement ou produit concerné'
                ]
            ])
            ->add('descriptionAnomalie', TextareaType::class, [
                'label' => 'Description de l\'anomalie',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Décrivez l\'anomalie observée'
                ]
            ])
            ->add('causeProbable', TextType::class, [
                'label' => 'Cause probable',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Cause probable de l\'anomalie'
                ]
            ])
            ->add('photoConstatFile', FileType::class, [
                'label' => 'Photo du constat',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG, GIF)',
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*'
                ]
            ])
            ->add('actionCloturee', ChoiceType::class, [
                'label' => 'Action clôturée',
                'choices' => [
                    'Oui' => 'oui',
                    'Non' => 'non'
                ],
                'placeholder' => 'Sélectionnez',
                'required' => false,
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('dateCloture', DateType::class, [
                'label' => 'Date clôture',
                'widget' => 'single_text',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('heureAction', TimeType::class, [
                'label' => 'Heure Action',
                'widget' => 'single_text',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('photoActionFile', FileType::class, [
                'label' => 'Photo d\'action clôturée',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG, GIF)',
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer mon rapport',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RapportHSE::class,
        ]);
    }
}
