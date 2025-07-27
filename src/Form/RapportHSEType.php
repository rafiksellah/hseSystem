<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\RapportHSE;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\EntityManagerInterface;

class RapportHSEType extends AbstractType
{
    private TokenStorageInterface $tokenStorage;
    private EntityManagerInterface $entityManager;

    public function __construct(TokenStorageInterface $tokenStorage, EntityManagerInterface $entityManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $token = $this->tokenStorage->getToken();
        $currentUser = $token ? $token->getUser() : null;
        $isAdminSelfReport = $options['is_admin_self_report'] ?? false;

        // Configuration du champ user selon le type d'utilisateur
        if ($isAdminSelfReport) {
            // Pour un admin qui crée son propre rapport, masquer le champ ou le rendre readonly
            $builder->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getCodeAgent() . ' - ' . $user->getNom() . ' ' . $user->getPrenom() . ' (' . $user->getZone() . ')';
                },
                'choice_value' => 'id',
                'label' => 'Agent (Vous-même)',
                'data' => $currentUser, // Pré-sélectionner l'utilisateur actuel
                'attr' => [
                    'class' => 'form-select',
                    'id' => 'user-select',
                    'readonly' => true
                ],
                'query_builder' => function (UserRepository $repository) use ($currentUser) {
                    // Montrer seulement l'utilisateur actuel
                    return $repository->createQueryBuilder('u')
                        ->andWhere('u.id = :currentUserId')
                        ->setParameter('currentUserId', $currentUser->getId());
                },
                'help' => 'En tant qu\'administrateur, vous ne pouvez créer des rapports que pour vous-même.'
            ]);
        } else {
            // Pour un super admin, afficher tous les utilisateurs possibles
            $builder->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getCodeAgent() . ' - ' . $user->getNom() . ' ' . $user->getPrenom() . ' (' . $user->getZone() . ')';
                },
                'choice_value' => 'id',
                'label' => 'Sélectionner un agent',
                'placeholder' => 'Choisissez un agent...',
                'attr' => [
                    'class' => 'form-select',
                    'id' => 'user-select'
                ],
                'query_builder' => function (UserRepository $repository) use ($currentUser) {
                    $qb = $repository->createQueryBuilder('u')
                        ->andWhere('u.roles NOT LIKE :admin')
                        ->andWhere('u.roles NOT LIKE :superAdmin')
                        ->setParameter('admin', '%ROLE_ADMIN%')
                        ->setParameter('superAdmin', '%ROLE_SUPER_ADMIN%')
                        ->orderBy('u.nom', 'ASC');

                    // Si l'utilisateur connecté est un admin (pas super admin), 
                    // ne montrer que les utilisateurs de sa zone
                    if (
                        $currentUser instanceof User &&
                        in_array('ROLE_ADMIN', $currentUser->getRoles()) &&
                        !in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles())
                    ) {
                        $qb->andWhere('u.zone = :zone')
                            ->setParameter('zone', $currentUser->getZone());
                    }

                    return $qb;
                }
            ]);
        }

        $builder
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
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('heure', TimeType::class, [
                'label' => 'Heure',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('zone', ChoiceType::class, [
                'label' => 'Zone de travail',
                'choices' => [], // Sera rempli dynamiquement
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

        // Listener pour initialiser les zones lors du chargement du formulaire
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $rapport = $event->getData();
            $form = $event->getForm();

            if ($rapport && $rapport->getUser()) {
                $userZone = $rapport->getUser()->getZone();
                $zones = RapportHSE::getZonesForUserZone($userZone);

                $form->add('zone', ChoiceType::class, [
                    'label' => 'Zone de travail',
                    'choices' => array_flip($zones), // array_flip pour avoir label => value
                    'placeholder' => 'Sélectionnez une zone',
                    'required' => false,
                    'attr' => [
                        'class' => 'form-select'
                    ]
                ]);
            }
        });

        // Listener pour mettre à jour les zones lors de la soumission
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (isset($data['user']) && $data['user']) {
                // Récupérer l'utilisateur sélectionné
                $user = $this->entityManager->getRepository(User::class)->find($data['user']);

                if ($user) {
                    $userZone = $user->getZone();
                    $zones = RapportHSE::getZonesForUserZone($userZone);

                    $form->add('zone', ChoiceType::class, [
                        'label' => 'Zone de travail',
                        'choices' => array_flip($zones),
                        'placeholder' => 'Sélectionnez une zone',
                        'required' => false,
                        'attr' => [
                            'class' => 'form-select'
                        ]
                    ]);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RapportHSE::class,
            'is_admin_self_report' => false,
        ]);
    }
}
