<?php

namespace App\Form;

use App\Entity\Clinique;
use App\Entity\Rendezvous;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class RendezvousType extends AbstractType
{
    public function __construct(private Security $security) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->security->getUser();
        $isConnected = $user !== null;

        /* ======================== CLIENT (PUBLIC) ======================== */
        if (!$isConnected) {
            $builder
                ->add('nomClient', TextType::class, [
                    'label' => 'Nom complet',
                    'required' => true,
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Ex : Jean Dupont'
                    ],
                ])
                ->add('emailClient', EmailType::class, [
                    'label' => 'Email',
                    'required' => true,
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'exemple@email.com'
                    ],
                ])
                ->add('telephoneClient', TelType::class, [
                    'label' => 'Téléphone',
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => '+216 XX XXX XXX'
                    ],
                ]);
        }

        /* ======================== ANIMAL ======================== */
        $builder
            ->add('nomAnimal', TextType::class, [
                'label' => 'Nom de l\'animal',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex : Max'
                ],
            ])
            ->add('especeAnimal', ChoiceType::class, [
                'label' => 'Espèce',
                'required' => true,
                'choices' => [
                    'Chien' => 'chien',
                    'Chat' => 'chat',
                    'Oiseau' => 'oiseau',
                    'Rongeur' => 'rongeur',
                    'Reptile' => 'reptile',
                    'Autre' => 'autre',
                ],
                'placeholder' => 'Sélectionnez une espèce',
                'attr' => ['class' => 'form-select'],
            ]);

        /* ======================== CLINIQUE ======================== */
        $builder->add('clinique', EntityType::class, [
            'class' => Clinique::class,
            'choice_label' => fn(Clinique $c) =>
                $c->getNom() . ' - ' . ($c->getVille() ?? $c->getAdresse()),
            'label' => 'Clinique',
            'placeholder' => 'Choisir une clinique',
            'required' => true,
            'query_builder' => fn($repo) =>
                $repo->createQueryBuilder('c')
                    ->where('c.actif = true')
                    ->orderBy('c.nom', 'ASC'),
            'attr' => ['class' => 'form-select'],
        ]);

        /* ======================== TYPE RENDEZ-VOUS ======================== */
        $builder->add('type', ChoiceType::class, [
            'label' => 'Type de rendez-vous',
            'required' => true,
            'choices' => [
                'Consultation' => 'consultation',
                'Vaccination' => 'vaccination',
                'Chirurgie' => 'chirurgie',
                'Urgence' => 'urgence',
                'Contrôle' => 'controle',
                'Stérilisation' => 'sterilisation',
                'Autre' => 'autre',
            ],
            'placeholder' => 'Sélectionnez le type',
            'attr' => ['class' => 'form-select'],
        ]);

        /* ======================== DATE ======================== */
        $builder->add('dateHeure', DateTimeType::class, [
            'label' => 'Date et heure',
            'widget' => 'single_text',
            'html5' => true,
            'required' => true,
            'attr' => [
                'class' => 'form-control'
            ],
        ]);

        /* ======================== NOTES CLIENT ======================== */
        $builder->add('notesClient', TextareaType::class, [
            'label' => 'Commentaire',
            'required' => false,
            'attr' => [
                'class' => 'form-control',
                'rows' => 4,
                'placeholder' => 'Décrivez brièvement la raison de la visite...',
            ],
        ]);

        /* ======================== ADMIN ======================== */
        if ($options['is_admin'] === true) {

            $builder->add('client', EntityType::class, [
                'class' => User::class,
                'choice_label' => fn(User $u) =>
                    $u->getFullName() . ' - ' . $u->getEmail(),
                'required' => false,
                'placeholder' => 'Sélectionner un client',
                'attr' => ['class' => 'form-select'],
            ]);

            $builder
                ->add('statut', ChoiceType::class, [
                    'label' => 'Statut',
                    'choices' => [
                        'En attente' => 'en_attente',
                        'Confirmé' => 'confirme',
                        'Annulé' => 'annule',
                        'Terminé' => 'termine',
                    ],
                    'attr' => ['class' => 'form-select'],
                ])
                ->add('montantPaiement', MoneyType::class, [
                    'label' => 'Montant',
                    'currency' => 'TND',
                    'required' => false,
                    'attr' => ['class' => 'form-control'],
                ])
                ->add('notesVeterinaire', TextareaType::class, [
                    'label' => 'Notes vétérinaire',
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control',
                        'rows' => 4,
                    ],
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
    'data_class' => Rendezvous::class,
    'is_admin' => false,
    'show_animal_select' => false,
]);

$resolver->setAllowedTypes('show_animal_select', 'bool');

    }
}
