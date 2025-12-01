<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['placeholder' => 'Entrez le prénom']
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Entrez le nom']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['placeholder' => 'exemple@email.com']
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['placeholder' => '06 12 34 56 78']
            ])
            ->add('address', TextareaType::class, [
                'label' => 'Adresse',
                'required' => false,
                'attr' => ['rows' => 3, 'placeholder' => 'Adresse complète']
            ]);

        // Pour la création d'utilisateur, afficher le champ mot de passe
        if ($options['is_creation']) {
            $builder->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => 'Mot de passe sécurisé'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                ],
            ]);
        }

        // Afficher les rôles selon les options
        if ($options['show_roles']) {
            $roleChoices = [
                '👤 Client (Propriétaire d\'animal)' => 'ROLE_USER',
                '🐾 Vétérinaire' => 'ROLE_VET',
                '⚙️ Administrateur' => 'ROLE_ADMIN', // Toujours visible et activé
            ];
            
            $builder->add('role', ChoiceType::class, [
                'label' => 'Type de compte',
                'choices' => $roleChoices,
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                'placeholder' => false,
                'mapped' => false,
                'data' => 'ROLE_USER',
                'help' => 'Sélectionnez le type de compte que vous souhaitez créer',
            ]);
        } else {
            // Pour les non-admins en édition, utiliser un champ caché
            $builder->add('role', HiddenType::class, [
                'data' => 'ROLE_USER',
                'mapped' => false,
            ]);
        }

        // Afficher la photo de profil seulement si show_profile_photo est true
        if ($options['show_profile_photo']) {
            $builder->add('profilePhoto', FileType::class, [
                'label' => 'Photo de profil',
                'required' => false,
                'mapped' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_creation' => false,
            'show_roles' => false,
            'show_profile_photo' => false,
            'allow_admin_role' => true, // MODIFICATION : TRUE pour permettre à tous
        ]);
    }
}