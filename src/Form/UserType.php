<?php
// src/Form/UserType.php - Version corrigée

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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

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
        $required = true;

        // Si l'utilisateur connecté est un admin (pas super admin), limiter les choix
        if ($currentUser && in_array('ROLE_ADMIN', $currentUser->getRoles()) && !$options['is_super_admin']) {
            // L'admin ne peut créer des utilisateurs que de sa propre zone
            $userZone = $currentUser->getZone();
            $zoneChoices = [$userZone => $userZone];
            $disabled = true;
            $help = 'Vous ne pouvez créer des utilisateurs que pour votre zone';
        }

        // Pour les super admins, la zone est optionnelle
        if ($options['is_super_admin']) {
            $help = 'Zone optionnelle pour les Super Administrateurs (ils peuvent gérer toutes les zones)';
            $required = false;
        }

        $builder->add('zone', ChoiceType::class, [
            'label' => 'Zone',
            'choices' => $zoneChoices,
            'placeholder' => $required ? ($disabled ? null : 'Sélectionnez une zone') : 'Aucune zone (Super Admin)',
            'required' => $required,
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
                    'multiple' => true,
                    'id' => 'user_roles'
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
                        'multiple' => true,
                        'id' => 'user_roles'
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

        // Ajouter un événement pour gérer dynamiquement la zone selon le rôle
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            // Si le rôle sélectionné est SUPER_ADMIN, la zone n'est pas obligatoire
            if (isset($data['roles']) && in_array('ROLE_SUPER_ADMIN', $data['roles'])) {
                // Permettre une zone vide pour les super admins
                if (empty($data['zone'])) {
                    $data['zone'] = null;
                    $event->setData($data);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
            'is_super_admin' => false,
        ]);
    }
}
