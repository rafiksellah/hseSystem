<?php

namespace App\Form;

use App\Entity\RapportHSE;
use App\Entity\User;
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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserRapportHSEType extends AbstractType
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $token = $this->tokenStorage->getToken();
        $currentUser = $token ? $token->getUser() : null;

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
            ]);

        // Obtenir les zones disponibles selon la zone de l'utilisateur
        $zonesDisponibles = $this->getZonesForUser($currentUser);

        $builder
            ->add('zone', ChoiceType::class, [
                'label' => 'Zone de travail',
                'choices' => $zonesDisponibles,
                'placeholder' => 'Sélectionnez une zone de travail',
                'required' => true,
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Sélectionnez la zone où l\'anomalie a été observée'
            ])
            ->add('emplacement', TextType::class, [
                'label' => 'Emplacement précis',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Emplacement précis dans la zone'
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
                    'placeholder' => 'Décrivez l\'anomalie observée en détail'
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

    /**
     * Obtient les zones disponibles selon l'utilisateur connecté et sa zone
     */
    private function getZonesForUser(?User $user): array
    {
        if (!$user) {
            return [];
        }

        $userZone = $user->getZone();

        // Retourner les zones de travail selon la zone de l'utilisateur
        return RapportHSE::getZonesForUserZone($userZone);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RapportHSE::class,
        ]);
    }
}