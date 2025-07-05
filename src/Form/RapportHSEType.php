<?php

namespace App\Form;

use App\Entity\RapportHSE;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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

class RapportHSEType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Utiliser l'ID au lieu du codeAgent comme choice_value
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getCodeAgent() . ' - ' . $user->getNom() . ' ' . $user->getPrenom();
                },
                'choice_value' => 'id',
                'label' => 'Sélectionner un agent',
                'placeholder' => 'Choisissez un agent...',
                'attr' => [
                    'class' => 'form-select',
                    'id' => 'user-select'
                ],
                'query_builder' => function (UserRepository $repository) {
                    return $repository->createQueryBuilder('u')
                        ->andWhere('u.roles NOT LIKE :admin')
                        ->setParameter('admin', '%ROLE_ADMIN%')
                        ->orderBy('u.nom', 'ASC');
                }
            ])
            ->add('codeAgt', TextType::class, [
                'label' => 'Code AGT',
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true,
                    'id' => 'codeAgt'
                ]
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom complet',
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true,
                    'id' => 'nomComplet'
                ]
            ])
            ->add('date', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                // Supprimer la valeur par défaut pour permettre le remplissage automatique
                // 'data' => new \DateTime(),
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true
                ]
            ])
            ->add('heure', TimeType::class, [
                'label' => 'Heure',
                'widget' => 'single_text',
                // Supprimer la valeur par défaut pour permettre le remplissage automatique
                // 'data' => new \DateTime(),
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true
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
                'label' => 'Enregistrer le rapport',
                'attr' => [
                    'class' => 'btn btn-success'
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
