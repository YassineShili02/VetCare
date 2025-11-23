<?php

namespace App\Form;

use App\Entity\Rendezvous;
use App\Entity\Veterinaire;
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

class RendezvousType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Informations Client
            ->add('nomClient', TextType::class, [
                'label' => 'Nom du client',
                'attr' => ['class' => 'form-control']
            ])
            ->add('emailClient', EmailType::class, [
                'label' => 'Email',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('telephoneClient', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])

            // Informations Animal
            ->add('nomAnimal', TextType::class, [
                'label' => 'Nom de l\'animal',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('especeAnimal', ChoiceType::class, [
                'label' => 'Espèce',
                'required' => false,
                'choices' => [
                    'Chien' => 'chien',
                    'Chat' => 'chat',
                    'Oiseau' => 'oiseau',
                    'Lapin' => 'lapin',
                    'Rongeur' => 'rongeur',
                    'Reptile' => 'reptile',
                    'Autre' => 'autre',
                ],
                'placeholder' => 'Sélectionner une espèce',
                'attr' => ['class' => 'form-control']
            ])

            // Type de rendez-vous
            ->add('type', ChoiceType::class, [
                'label' => 'Type de rendez-vous',
                'choices' => [
                    'Consultation générale' => 'consultation',
                    'Vaccination' => 'vaccination',
                    'Chirurgie' => 'chirurgie',
                    'Urgence' => 'urgence',
                    'Contrôle' => 'controle',
                    'Stérilisation' => 'sterilisation',
                    'Dentaire' => 'dentaire',
                    'Autre' => 'autre',
                ],
                'attr' => ['class' => 'form-control']
            ])

            // Vétérinaire (optionnel)
            ->add('veterinaire', EntityType::class, [
                'class' => Veterinaire::class,
                'choice_label' => function(Veterinaire $vet) {
                    return "Dr. {$vet->getPrenom()} {$vet->getNom()}" .
                        ($vet->getSpecialite() ? " - {$vet->getSpecialite()}" : '');
                },
                'label' => 'Vétérinaire (optionnel)',
                'placeholder' => 'Aucune préférence',
                'required' => false,
                'query_builder' => function($er) {
                    return $er->createQueryBuilder('v')
                        ->where('v.actif = :actif')
                        ->setParameter('actif', true)
                        ->orderBy('v.nom', 'ASC');
                },
                'attr' => ['class' => 'form-control']
            ])

            // Date et heure
            ->add('dateHeure', DateTimeType::class, [
                'label' => 'Date et heure',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])

            // Notes du client
            ->add('notesClient', TextareaType::class, [
                'label' => 'Notes / Raison de la visite',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Décrivez le motif de votre visite...'
                ]
            ]);

        // Champs admin
        if ($options['is_admin']) {
            $builder
                ->add('statut', ChoiceType::class, [
                    'label' => 'Statut',
                    'choices' => [
                        'En attente' => 'en_attente',
                        'Confirmé' => 'confirme',
                        'Refusé' => 'refuse',
                        'Terminé' => 'termine',
                        'Annulé' => 'annule',
                    ],
                    'attr' => ['class' => 'form-control']
                ])
                ->add('notesVeterinaire', TextareaType::class, [
                    'label' => 'Notes vétérinaire (privées)',
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control',
                        'rows' => 4,
                        'placeholder' => 'Notes médicales privées...'
                    ]
                ])
                ->add('statutPaiement', ChoiceType::class, [
                    'label' => 'Statut de paiement',
                    'choices' => [
                        'Non payé' => 'non_paye',
                        'Payé' => 'paye',
                        'Partiel' => 'partiel',
                        'Remboursé' => 'rembourse',
                    ],
                    'attr' => ['class' => 'form-control']
                ])
                ->add('montantPaiement', MoneyType::class, [
                    'label' => 'Montant',
                    'required' => false,
                    'currency' => 'TND',
                    'attr' => ['class' => 'form-control']
                ])
                ->add('methodePaiement', ChoiceType::class, [
                    'label' => 'Méthode de paiement',
                    'required' => false,
                    'choices' => [
                        'Espèces' => 'especes',
                        'Carte bancaire' => 'carte',
                        'Chèque' => 'cheque',
                        'Virement' => 'virement',
                    ],
                    'placeholder' => 'Sélectionner',
                    'attr' => ['class' => 'form-control']
                ]);
        }
    }

    // 👉 La bonne place, ici en dehors de buildForm()
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rendezvous::class,
            'is_admin' => false,
        ]);
    }
}
