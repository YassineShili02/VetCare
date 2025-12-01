<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
<<<<<<< HEAD
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
=======
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
>>>>>>> 0693e84bb198da9483ca0f754855e4147ce8b3ee

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'Mot de passe actuel',
                'attr' => [
                    'class' => 'form-control',
<<<<<<< HEAD
                    'placeholder' => 'Entrez votre mot de passe actuel',
                    'autocomplete' => 'current-password'
=======
                    'placeholder' => 'Entrez votre mot de passe actuel'
>>>>>>> 0693e84bb198da9483ca0f754855e4147ce8b3ee
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre mot de passe actuel',
                    ]),
                ],
<<<<<<< HEAD
                'row_attr' => [
                    'class' => 'mb-3'
                ]
=======
>>>>>>> 0693e84bb198da9483ca0f754855e4147ce8b3ee
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent correspondre.',
<<<<<<< HEAD
                'options' => [
                    'attr' => [
                        'class' => 'form-control',
                        'autocomplete' => 'new-password'
                    ]
                ],
=======
                'options' => ['attr' => ['class' => 'form-control']],
>>>>>>> 0693e84bb198da9483ca0f754855e4147ce8b3ee
                'required' => true,
                'first_options'  => [
                    'label' => 'Nouveau mot de passe',
                    'attr' => [
                        'class' => 'form-control',
<<<<<<< HEAD
                        'placeholder' => 'Entrez votre nouveau mot de passe (min. 6 caractères)',
                        'data-strength-meter' => 'true'
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
                        new Regex([
                            'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
                            'message' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre',
                            'match' => false,
                        ]),
                    ],
                    'row_attr' => [
                        'class' => 'mb-3'
                    ]
                ],
                'second_options' => [
                    'label' => 'Confirmation du mot de passe',
                    'attr' => [
                        'class' => 'form-control', 
                        'placeholder' => 'Confirmez votre nouveau mot de passe'
                    ],
                    'row_attr' => [
                        'class' => 'mb-3'
                    ]
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Changer le mot de passe',
                'attr' => [
                    'class' => 'btn btn-primary w-100 mt-3',
                    'id' => 'change-password-btn'
                ]
=======
                        'placeholder' => 'Entrez votre nouveau mot de passe'
                    ]
                ],
                'second_options' => [
                    'label' => 'Répétez le nouveau mot de passe',
                    'attr' => [
                        'class' => 'form-control', 
                        'placeholder' => 'Confirmez votre nouveau mot de passe'
                    ]
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
>>>>>>> 0693e84bb198da9483ca0f754855e4147ce8b3ee
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
<<<<<<< HEAD
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'change_password',
=======
            // Pas de data_class car ce n'est pas une entité
>>>>>>> 0693e84bb198da9483ca0f754855e4147ce8b3ee
        ]);
    }
}