<?php

namespace App\Form;

use App\Entity\Animal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnimalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('type_animal')
            ->add('sexe', ChoiceType::class, [
                'choices' => [
                    'M' => 'M',
                    'F' => 'F',
                ],
                'expanded' => true, // radio buttons
                'multiple' => false,
                'label' => 'Sexe',
            ])
            ->add('poids', NumberType::class)
            ->add('couleur')
            ->add('date_naissance', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de Naissance',
                'html5' => false,
                'required' => true,
                'input' => 'datetime', // <-- Important !
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Animal::class,
        ]);
    }
}
