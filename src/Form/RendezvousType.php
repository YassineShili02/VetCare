<?php

namespace App\Form;

use App\Entity\Clinique;
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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RendezvousType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Informations Client
            ->add('nomClient', TextType::class, [
                'label' => 'Nom complet',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Jean Dupont'
                ]
            ])
            ->add('emailClient', EmailType::class, [
                'label' => 'Email',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'exemple@email.com'
                ]
            ])
            ->add('telephoneClient', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '+216 XX XXX XXX'
                ]
            ])

            // Informations Animal
            ->add('nomAnimal', TextType::class, [
                'label' => 'Nom de l\'animal',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Max'
                ]
            ])
            ->add('especeAnimal', ChoiceType::class, [
                'label' => 'Espèce',
                'required' => false,
                'choices' => [
                    'Chien' => 'chien',
                    'Chat' => 'chat',
                    'Oiseau' => 'oiseau',
                    'Rongeur' => 'rongeur',
                    'Reptile' => 'reptile',
                    'Autre' => 'autre',
                ],
                'placeholder' => 'Sélectionnez une espèce',
                'attr' => ['class' => 'form-select']
            ])

            // Type de rendez-vous
            ->add('type', ChoiceType::class, [
                'label' => 'Type de rendez-vous',
                'required' => true,
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
                'placeholder' => 'Sélectionnez le type de consultation',
                'attr' => ['class' => 'form-select']
            ])

            // Date et heure
            ->add('dateHeure', DateTimeType::class, [
                'label' => 'Date et heure',
                'widget' => 'single_text',
                'disabled' => true,
                'attr' => ['class' => 'form-control']
            ])

            // Notes du client
            ->add('notesClient', TextareaType::class, [
                'label' => 'Notes ou commentaires',
                'required' => false,
                'help' => 'Décrivez brièvement la raison de votre visite ou toute information utile',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Ex: Mon chat tousse depuis 3 jours...'
                ]
            ]);

        // Champs admin uniquement
        if ($options['is_admin']) {
            $builder
                ->add('statut', ChoiceType::class, [
                    'label' => 'Statut',
                    'required' => true,
                    'choices' => [
                        'En attente' => 'en_attente',
                        'Confirmé' => 'confirme',
                        'Refusé' => 'refuse',
                        'Terminé' => 'termine',
                        'Annulé' => 'annule',
                    ],
                    'attr' => ['class' => 'form-select']
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
                    'required' => true,
                    'choices' => [
                        'Non payé' => 'non_paye',
                        'Payé' => 'paye',
                        'Partiel' => 'partiel',
                        'Remboursé' => 'rembourse',
                    ],
                    'attr' => ['class' => 'form-select']
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
                        'Carte bancaire' => 'carte_bancaire',
                        'Chèque' => 'cheque',
                        'Virement' => 'virement',
                    ],
                    'placeholder' => 'Sélectionner une méthode',
                    'attr' => ['class' => 'form-select']
                ]);
        }

        // Ajout du champ Clinique (non mappé)
        $builder->add('clinique', EntityType::class, [
            'class' => Clinique::class,
            'choice_label' => 'nom',
            'label' => 'Clinique',
            'placeholder' => 'Choisir une clinique',
            'required' => false,
            'mapped' => false,
            'disabled' => true,
            'query_builder' => function ($repository) {
                return $repository->createQueryBuilder('c')
                    ->where('c.actif = :actif')
                    ->setParameter('actif', true)
                    ->orderBy('c.nom', 'ASC');
            },
            'attr' => ['class' => 'form-select']
        ]);

        // Fonction pour modifier dynamiquement le champ vétérinaire
        $formModifier = function (FormInterface $form, ?Clinique $clinique = null) {
            $veterinaires = null === $clinique 
                ? [] 
                : $clinique->getVeterinaires()->filter(fn($v) => $v->isActif())->toArray();

            $form->add('veterinaire', EntityType::class, [
                'class' => Veterinaire::class,
                'choice_label' => function(Veterinaire $vet) {
                    return "Dr. {$vet->getPrenom()} {$vet->getNom()}" .
                        ($vet->getSpecialite() ? " - {$vet->getSpecialite()}" : '');
                },
                'label' => 'Vétérinaire (optionnel)',
                'placeholder' => $clinique ? 'Aucune préférence' : 'Sélectionnez d\'abord une clinique',
                'required' => false,
                'choices' => $veterinaires,
                'disabled' => true,
                'attr' => ['class' => 'form-select']
            ]);
        };

        // Événement PRE_SET_DATA : initialisation du formulaire
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $rendezvous = $event->getData();
                $clinique = $rendezvous?->getVeterinaire()?->getClinique();
                
                if ($clinique) {
                    $form = $event->getForm();
                    $form->get('clinique')->setData($clinique);
                }
                
                $formModifier($event->getForm(), $clinique);
            }
        );

        // Événement POST_SUBMIT : changement de clinique
        $builder->get('clinique')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $clinique = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $clinique);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rendezvous::class,
            'is_admin' => false,
        ]);
    }
}