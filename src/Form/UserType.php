<?php
// src/Form/UserType.php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserType extends AbstractType
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
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le nom'
                ]
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le prénom'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez l\'email'
                ]
            ])
            ->add('codeAgent', TextType::class, [
                'label' => 'Code Agent',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le code agent'
                ]
            ]);

        // Configuration du champ zone selon le rôle de l'utilisateur connecté
        $zoneChoices = ['SIMTIS' => 'SIMTIS', 'SIMTIS TISSAGE' => 'SIMTIS TISSAGE'];
        $disabled = false;
        $help = null;

        // Si l'utilisateur connecté est un admin (pas super admin), limiter les choix
        if ($currentUser && in_array('ROLE_ADMIN', $currentUser->getRoles()) && !$options['is_super_admin']) {
            // L'admin ne peut créer des utilisateurs que de sa propre zone
            $userZone = $currentUser->getZone();
            $zoneChoices = [$userZone => $userZone];
            $disabled = true;
            $help = 'Vous ne pouvez créer des utilisateurs que pour votre zone';
        }

        $builder->add('zone', ChoiceType::class, [
            'label' => 'Zone',
            'choices' => $zoneChoices,
            'placeholder' => $disabled ? null : 'Sélectionnez une zone',
            'required' => true,
            'attr' => [
                'class' => 'form-select'
            ],
            'disabled' => $disabled,
            'help' => $help
        ]);

        // Ajouter le champ rôles pour la création si super admin
        if (!$options['is_edit'] && $options['is_super_admin']) {
            $builder->add('roles', ChoiceType::class, [
                'label' => 'Rôles',
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                    'Super Administrateur' => 'ROLE_SUPER_ADMIN'
                ],
                'multiple' => true,
                'expanded' => false,
                'data' => ['ROLE_USER'], // Valeur par défaut
                'attr' => [
                    'class' => 'form-select',
                    'multiple' => true
                ],
                'help' => 'Maintenez Ctrl (Cmd sur Mac) pour sélectionner plusieurs rôles'
            ]);
        }

        // Ajouter le champ mot de passe seulement si ce n'est pas une édition
        if (!$options['is_edit']) {
            $builder->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                ],
                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Entrez le mot de passe'
                    ]
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Confirmez le mot de passe'
                    ]
                ],
            ]);
        } else {
            // Pour l'édition, mot de passe optionnel
            $builder->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                ],
                'first_options' => [
                    'label' => 'Nouveau mot de passe (optionnel)',
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Laissez vide pour conserver l\'ancien'
                    ]
                ],
                'second_options' => [
                    'label' => 'Confirmer le nouveau mot de passe',
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Confirmez le nouveau mot de passe'
                    ]
                ],
            ]);

            // Ajouter le champ rôles pour l'édition (seulement pour SUPER_ADMIN)
            if ($options['is_super_admin']) {
                $builder->add('roles', ChoiceType::class, [
                    'label' => 'Rôles',
                    'choices' => [
                        'Utilisateur' => 'ROLE_USER',
                        'Administrateur' => 'ROLE_ADMIN',
                        'Super Administrateur' => 'ROLE_SUPER_ADMIN'
                    ],
                    'multiple' => true,
                    'expanded' => false,
                    'attr' => [
                        'class' => 'form-select',
                        'multiple' => true
                    ],
                    'help' => 'Maintenez Ctrl (Cmd sur Mac) pour sélectionner plusieurs rôles'
                ]);
            }
        }

        $builder->add('save', SubmitType::class, [
            'label' => $options['is_edit'] ? 'Modifier l\'utilisateur' : 'Créer l\'utilisateur',
            'attr' => [
                'class' => 'btn btn-primary w-100'
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
            'is_super_admin' => false, // Ajouter cette option
        ]);
    }
}